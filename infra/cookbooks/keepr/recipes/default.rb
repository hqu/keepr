#
# Cookbook Name:: keepr
# Recipe:: default
#
# Copyright 2013, YOUR_COMPANY_NAME
#
# All rights reserved - Do Not Redistribute
#

include_recipe "iptables"
include_recipe "php"

iptables_rule "httpd"
iptables_rule "ssh"

#install php-dom
package "php-xml" do
	action :install
end

service "httpd" do
	action :restart # see actions section below
end

# install the mongodb pecl
php_pear "mongo" do
  action :install
end