## LowEndPing Network Status

LowEndPing is a simple network query script.

Installation:

- Install Composer
- Download the contents and extract into a directory
- Run "composer install" in that directory
- Run "php artisan migrate" to initialize the database
- Install the python script on the servers you want (see below)
- Edit app/config/lowendping.php and add the servers (Numeric keys are required, and it is recommended to keep them in order), and optionally disable the archive
- Add "cd /path/to/lowendping; php artisan lowendping:archive" to a cron job, every hour or daily would be fine. This is required even without the archive to clean up old queries.
- Edit your webserver configuration and set the document root to (install dir)/public, then add a rewrite rule for laravel (Use google, there's plenty out there)
- Try it out!

Python script installation:

- Install python, python-pip, and mtr-tiny
- Run 'pip install sh' (the required shell wrapper) and 'pip install ipaddress' (required only if python 2.7 instead of python 3)
- Update the information in lepconf.py to point to your server and define an auth token
- Run 'python lep.py' (It is recommended to run it as a user other than root of course)

### Websockets

Websockets will allow LowEndPing to give instant responses. It requires a bit more configuration, and is probably only really worth it when running a LowEndPing installation with more than 10 servers or a lot of traffic.

To use Websockets, install the following packages (along with a c compiler, gcc will work just fine):

Debian:

	apt-get install libzmq1 libzmq-dev

Then, install php-zmq


	git clone git://github.com/mkoppanen/php-zmq.git
	cd php-zmq
	phpize && ./configure
	make && make install
	echo "extension=zmq.so" > /etc/php5/conf.d/zmq.ini


To install the libraries needed in PHP you must add the following to composer.json's "require" section, and run "composer update"


	"cboden/Ratchet": "0.3.*",
	"react/zmq": "0.2.*"


And then start "php artisan lowendping:websocket" and restart your webserver/php5.

Then, set websocket.enabled to "true" in app/config/lowendping.php, and modify the port if you wish. Websockets should now work, verify it by opening your url and the Network console in Chrome/Firefox, and watch for "Switching Protocols" when submitting a query.

It is also suggested to start the server using supervisord, just any standard config running the above command will work.

### License

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

LowEndPing is also open-source and licensed under the same license.