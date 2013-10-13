#
# Cookbook Name:: phpunit
# Recipe:: phar
#
# Copyright 2012-2013, Escape Studios
#

if node[:phpunit][:install_dir] != ""
    phpunit_dir = node[:phpunit][:install_dir]
else
    phpunit_dir = "#{Chef::Config[:file_cache_path]}/phpunit"
end

directory "#{phpunit_dir}" do
    owner "root"
    group "root"
    mode "0755"
    action :create
end

remote_file "#{phpunit_dir}/phpunit.phar" do
    source node[:phpunit][:phar_url]
    mode "0755"
end