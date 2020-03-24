# Tool to generate table files

Usage: `python scrape_sha.py table_file`
The tool must be run in the same directory as a beatoraja song.db with all songs in the tables you want to add loaded.
Copy the output from converted\_tables to tables/ on the webserver. 

Example table file:

```
    *REM	URL	Name	Symbol	file
    http://fuki1755.starfree.jp/fox_table/fox_table.html	Σ：3 」 ∠ )ﾐ⌒ゞ	Σ	fox_table.json
    http://kusefumen.web.fc2.com/kuse/kuse_nannido.html	癖譜面コレクション(仮)	!?	kuse1.json
    http://rattoto10.web.fc2.com/kusekore_sub/list_sample.html	癖譜面コレクション(サブ)	¿¡	kuse2.json
    https://stellabms.xyz/table.html	Stella	st	stella.json
    https://lite.stellabms.xyz/table.html	Satellite	sl	satellite.json
```
