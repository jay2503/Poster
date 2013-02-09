About
-----------
This is developer tool for interacting with web services and other web resources that lets you make HTTP requests, set the parameters of Post and Get methods, also headers. 

Extra feature than other Poster Application,
- Maintain history of WS calls
- Reopen WS call History calls with Poster
- Keep Parameter name history, and provides Auto-complete feature
- Provides Auto-complete feature in URLs
- Response can be view as HTML as well as Raw
- Simple and cool UI using jQueryUI open source
- jQuery support for further custom enhancement
- Mac, Linux and Windows supported shortcuts to speed up!


Dependencies
-----------
Below are the dependencies in order to use this Application.
- [XAMPP][xmp] 

or

- Apache
- PHP
- MySQL
 
Installation
------------

Assuming Dependencies already installed on your machine.

- Clone or Download Git Repo 

- Make virtual host,
Open, httpd-vhosts.conf file and copy below lines at the EOF
Where "/Path/to/Poster/GitClone/" would be your Path of Poster source code.
	
```
<VirtualHost *:80>
	DocumentRoot /Path/to/Poster/GitClone/
	ServerName my.poster.com
	<Directory /Path/to/Poster/GitClone/>
		AllowOverride All
		Order deny,allow
		Allow from all
	</Directory>
</VirtualHost>
```

- Make entry in hosts file

	`127.0.0.1	my.poster.com`
	
- Restart Apache server	
- Make DB under MySQL server name it "Poster"
- Import poster.sql in "Poster" DB.
- Open config.php file and edit DB related changes.
- Check http://my.poster.com is working fine or not.


[xmp]: http://sourceforge.net/projects/xampp/