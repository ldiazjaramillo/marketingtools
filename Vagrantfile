# -*- mode: ruby -*- vi: set ft=ruby :
Vagrant.configure("2") do |config|
    config.vm.box = "scotch/box"
    config.vm.network "private_network", ip: "192.168.31.31"
    config.vm.hostname = "marketingtools"
    config.vm.synced_folder ".", "/var/www", :mount_options => ["dmode=777", "fmode=666"]

    config.vm.provider :virtualbox do |vb|
        vb.customize ["modifyvm", :id, "--memory", "512"]
	vb.customize ["modifyvm", :id, "--cpus", "1"]
    end

    # Optional NFS. Make sure to remove other synced_folder line too config.vm.synced_folder ".", "/var/www", :nfs => { :mount_options => ["dmode=777","fmode=666"] }
end
