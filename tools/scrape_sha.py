from requests import get as requests_get
from requests.exceptions import ConnectionError
from requests.exceptions import ChunkedEncodingError
from lxml import etree
from io import BytesIO
import json
import sys
import sqlite3

def get_retry(page, mode='HTML', encoding='default'):
	active_page = False
	attempt_count = 0
	while not active_page:
		try:
			active_page = requests_get(page)
			if mode == 'HTML':
				if encoding != 'default':
					parser = etree.HTMLParser(encoding=encoding)
				else:
					parser = etree.HTMLParser()
				active_page = etree.parse(BytesIO(active_page.content), parser)
			elif mode == 'JSON':
				active_page.encoding = active_page.apparent_encoding
				active_page = json.loads(active_page.text)
			attempt_count += 1
		except (ConnectionError, ChunkedEncodingError):
			if attempt_count > 10:
				print("Gave up on page", page)
				return False
	return active_page

def getPage(page):
	r = get_retry(page['link'], encoding='cp932')
	table = r.xpath('/html/body/div[1]/div[1]/div[1]/table[4]/tr')
	totals = {'all': 0}
	for num in range(1, len(table)):
		data = table[num].findall('.//td')
		if len(data) <= 10: #comment entries
			continue
		totals['all'] += 1
		op = data[14].text
		if op not in totals:
			totals[op] = 0
		totals[op] += 1
	page['totals'] = totals
	return page

if __name__ == '__main__':
	if len(sys.argv) < 2:
		print('No input specified')
		exit()

	with open(sys.argv[1], 'r', encoding='utf-8') as f:
		tables = f.readlines()
#	index = []

	conn = sqlite3.connect('songdata.db')
	cursor = conn.cursor()
	print('Opened song database')

	for table_data in tables:
		if table_data[0:4] != '*REM':
			table, name, symbol, file = table_data.rstrip().split("\t")
			url = table if table[0] != '*' else table[1:]
#			index.append({'name': name, 'url': url, 'symbol': symbol, 'file': file})
		if table_data[0] == '*':
			continue
		print(name)
		print(symbol)
		print(table)
		r = get_retry(table, mode='HTML')
		header = r.xpath('//meta[@name="bmstable"]')[0].get('content')
		if 'http' not in header:
			header = '/'.join(table.split('/')[:-1]) + '/' + header
		header_data = get_retry(header, mode='JSON')
		data_url = header_data['data_url']
		if 'http' not in data_url:
			data_url = '/'.join(table.split('/')[:-1]) + '/' + data_url
		r = get_retry(data_url, mode='JSON')
		if len(r) == 0:
			continue
		sub_field = 'subartist' if 'subartist' in r[0] else ('name_diff' if 'name_diff' in r[0] else 'artist')

		if 'sha256' not in r[0]:
			failed = False
			for data in r:
				cursor.execute('SELECT sha256 FROM song WHERE md5 = ?', (data['md5'],))
				result = cursor.fetchone()
				if result is None:
					print('You are missing at least one song from this table. Skipping. Missing song: ', data)
					failed = True
					break
				data['sha256'] = result[0] #if this errors, missing a song
			if failed: continue

		f = open('converted_tables/' + file, 'w')
		data = {'name': name, 'symbol': symbol, 'songdata': list(r)}
		f.write(json.dumps(data))
		f.close()
		print("Done")
		# exit()
#	f = open('index.json', 'w')
#	f.write(json.dumps(index))
#	f.close()
	conn.close()