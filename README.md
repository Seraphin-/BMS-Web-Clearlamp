# Clearlamp
Fork of XYZ's clearlamp tool that supports beatoraja databases. You must instead provide versions of supported tables with sha256 hashes in the tables/ directory. There is a tool to generate them from the beatoraja song.db provided in tools/scrape\_sha.py. Additionally, you must make a directory dbs/ on the webserver. Make sure it has write permissions to the folder.
You may also want to adjust the filesize limit in upload\_db.php and set up a script to auto prune old databases after a certain time. 
