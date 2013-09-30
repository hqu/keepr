Keepr is a tool for journalists to data-mine social media conversations for breaking news highlights.

It's a responsive web design built with ZURB Foundation framework.

Data is pulled from Twitter API, Tumblr API, and other social network APIs.

http://www.keepr.com


Development
=========

For development server we use Vagrant http://www.vagrantup.com/. Once installed you can just do `vagrant up`
and everything should be set for you to get going!

Once vagrant is up and running you can access the site from your browser. `http://localhost:12345`.
If the port is collided with one of your port you can change that in Vagrantfile 

`config.vm.network :forwarded_port, guest: 80, host: 12345`
