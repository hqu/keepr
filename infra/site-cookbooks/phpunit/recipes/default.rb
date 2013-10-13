#
# Cookbook Name:: phpunit
# Recipe:: default
#
# Copyright 2012-2013, Escape Studios
#

case node[:phpunit][:install_method]
    when "pear"
        include_recipe "phpunit::pear"
    when "composer"
        include_recipe "phpunit::composer"
    when "phar"
        include_recipe "phpunit::phar"
end