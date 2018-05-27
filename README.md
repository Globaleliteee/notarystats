## Notarization Monitoring with simplenode

### Prerequisites

Have a VPS / Webserver, all was installed on a fresh ubuntu 16.04

How this works : 
	Basically, you'll have a server with your coins running, on this node, you'll gather info, then send it to your webserver via SCP which will automatically parse it on a webpage. You'll just have to decide the frequency you want it to run on by adjusting the crontab on your node.

## Node Server - What to do : 

### Get the repo and change it to match your setup

	git clone https://github.com/dwygit/notarystats.git
	cd simplenode
	
Open the simplenode file :

	vi simplenode 
Change the params at the end of the script : Basically here, you'll have to make sure those lines on simplenode match the place where you have your files.

	pwdvalue="/home/$USER/script"
	homeuser=$(echo '/home/$USER/')
	komodo_binary=$(echo $homeuser"komodo/src/komodod")
	komodo_cli=$(echo $homeuser"komodo/src/komodo-cli")
	komodo_binary_dir=$(echo $homeuser"komodo/src")
	komodo_binary_name='komodod'
	chips_binary=$(echo $homeuser"chips3/src/chipsd")
	chips_cli=$(echo $homeuser"chips3/src/chips-cli")
	chips_binary_dir=$(echo $homeuser"chips3/src")
	chips_binary_name='chipsd'
	gamecredits_binary=$(echo $homeuser"GameCredits/src/gamecreditsd")
	gamecredits_cli=$(echo $homeuser"GameCredits/src/gamecredits-cli")
	gamecredits_binary_dir=$(echo $homeuser"GameCredits/src")
	gamecredits_binary_name='gamecreditsd'



Then you'll have to tell where you want your node will send the json's : 
**At the end of the simplenode script, you'll have a scp command, change the last part to match your webserver** (in the setup described here, user is *dwy*, and host is called *webstats*, and the scp sends on the webserver on path */var/www/html/json*: 

	scp $(echo "$pwdvalue")/jsontosend/$(echo "$jsondatefilestamp").json dwy@webstats:/var/www/html/json


### Create a new specific SSH Keypair for stats loading

**No password on the specific key for scp to just fire it without any prompt**
**Name it webstats** (or what you just decide, just make sure it matches the *IdentityFile* below)

	ssh-keygen

When the key is created, you can check it via 
	
	ll ~/.ssh/

In our example, you should see **webstats** and **webstats.pub**
Extract your pubkey to add it to your webserver, that's this point that will allow your node to send via SCP using a key.

	cat ~/.ssh/webstats.pub

The output is the key you'll have to add on your webserver.

### Specify Host

Create the file ~/.ssh/config

	vi ~/.ssh/config

Then Put the info on it : 

	Host webstats
	HostName "IP OF YOUR WEBSERVER GATHERING STATS"
	User dwy
	IdentityFile ~/.ssh/webstats

What this means ? : It will bind the name webstats when you'll call it via ssh/scp command, and it will bind it to the IP you specified, use the user you specified, and the privatekey you specified via IdentityFile.
  

## WEBSERVER - Setup

### Updating Server

	sudo apt-get -y update && sudo apt-get -y upgrade


### Adding user

As we did before, this setup is with user dwy, change it to whatever you want, just make sure it matches the *~/.ssh/config* of your Node (done previously).

	adduser dwy
	usermod -aG sudo dwy
	su - dwy

### SSH
Let's configure SSH : 
Disable root login on your webserver via ssh : 

	sudo vi /etc/ssh/sshd_config

Make sure that root login is disabled.
	
	PermitRootLogin no
	
Then restart ssh : 

	sudo service ssh restart

Now we'll add the key of your Node under the user you specified on the config file previously : 

	su - dwy
	mkdir .ssh
	chmod 700 .ssh
	sudo vim .ssh/authorized_keys

Pase your SSH Public key there : 

	ssh-rsa YOUR PUBLIC SSH KEY GENERATED SPECIFICALLY ON THE NN SERVER
	
Then restart ssh : 

	sudo service ssh restart

## General config on webserver
### Disable IPV6 (optional)

	sudo vi /etc/sysctl.conf

Paste this at the end of the file, then save :

	net.ipv6.conf.all.disable_ipv6 = 1
	net.ipv6.conf.default.disable_ipv6 = 1
	net.ipv6.conf.lo.disable_ipv6 = 1
	
Then apply : 

	sudo sysctl -p

### Fail2ban (Optional)

	sudo apt-get install fail2ban
	awk '{ printf "# "; print; }' /etc/fail2ban/jail.conf | sudo tee /etc/fail2ban/jail.local
	sudo vi /etc/fail2ban/jail.local

Add those lines if you want to be sure your ip won't be banned, ever.

	[DEFAULT]
	ignoreip = THE IP YOU WANT NEVER TO BE BANNED, IF THERE IS ONE :)


### Firewall (optional)

	sudo apt-get install iptables-persistent
	sudo service fail2ban stop
	sudo iptables -A INPUT -i lo -j ACCEPT
	sudo iptables -A INPUT -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT
	sudo iptables -A INPUT -p tcp --dport 22 -j ACCEPT
	sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
	sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT
	sudo iptables -A INPUT -j DROP
	sudo dpkg-reconfigure iptables-persistent
	sudo service fail2ban start

### NGINX and PHP

	sudo apt-get install nginx
	sudo apt-get install php-fpm
	sudo vi /etc/php/7.0/fpm/php.ini
Change cgi.fix_pathinfo to 0

	cgi.fix_pathinfo=0

Then restart php

	sudo systemctl restart php7.0-fpm

Now we'll tel your webserver where we'll store the files : 

	sudo vi /etc/nginx/sites-available/default

Paste this : 

	server {
    listen 80 default_server;
    listen [::]:80 default_server;
    root /var/www/html;
    index index.php index.html index.htm index.nginx-debian.html;
    server_name server_domain_or_IP;
    location / {
        try_files $uri $uri/ =404;
    }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php7.0-fpm.sock;
    }
    location ~ /\.ht {
        deny all;
    }
	}

Then reload webserver : 

	sudo systemctl reload nginx

### Placing the webserver's files

I assume you have either a DN that points to your webserver, or you'll just access it via its ip. Security on it via specific modules is up to you.

Now it's time to put files in the good folders to get the webpage in place.

Take the **webserver** folder from repo, and put it into your **/var/www/html** folder.

Test your webserver via http://YOURWEBSERVERIP (or your DN)

You should see the webpage without any info... Yet :)

In case it s not done, just create the json folder : 

	sudo mkdir /var/www/html/json

### Finishing

Ok, now you got : the simplenode script on your Node Server, and the files properly placed on your webserver.

Last step is to put everything in place to get it working.

First, we'll just set things up manually on the Node : 

 - 1st check : Your connectivity with Webserver : On the Node Server, issue a :

		ssh webstats

If you can connect and it doesn't ask for a password, it's ok, your key is well configured in both servers. If not, review your ~/.ssh/config file, make sure you explicitly put the specific key on the IdentityFile, your WEBSERVER IP is correct, and you added your public key to the webserver's authorized_keys.


 - 2nd check : You have the good target on the simplenode file (as seen previously, if you tweaked it, make sure you changed the  scp line below):

		scp $(echo "$pwdvalue")/jsontosend/$(echo "$jsondatefilestamp").json dwy@webstats:/var/www/html/json

 - 3rd check : Check that you have everything recognized (on the Node): 

		cd
		cd notarystats/simplenode
		./simplenode shouldrun

If that's not the case, it's simply because you may have launched the coins with different params, basically, it depends on the isrunning function, tweak the isrunning to match your needs, then when you'll have the shouldrun all green, you're ready to go.

Then now launch the sending of the json to your webserver : 
		
	./simplenode json

Now we'll control that your Node has created the Json, then we'll check that the webserver received it : 

	cd notarystats/simplenode/jsontosend
	ll

You got a file timestamped, you're good :) Now check the on the webserver : 

	cd /var/www/html/json
	ll

A file appeared ! Everything works now :).

Test your webserver via http://YOURWEBSERVERIP (or your DN) : You should now  see the updated stats.


### Very Last step : cron it (ONLY WHEN IT WORKS MANUALLY)

Go to your script folder (where Simplenode is located)

	cd notarystats/simplenode/
	vi simplecron
Then paste this (replacing the user): 

	#!/bin/bash
	pwdvalue="/home/dwy/notarystats/simplenode"

	if [ $(eval "pgrep -f 'simplenode'" | wc -l) == "1" ]
	then
			echo "$(date +%s) - ERROR : ALREADY RUNNING" >> $pwdvalue/lazylog/checklog
	else
			/home/dwy/notarystats/simplenode json
	fi


Add simplecron to your crontab : 

	crontab -e
Then paste the line below (you can check it on the frequency you want just by changing the cron, in my case it launches every 20 min)

	*/20 * * * * /home/dwy/notarystats/simplecron