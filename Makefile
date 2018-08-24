PROJECTNAME=im-server
INSTALLDIR=/data/im-server

all: update

install:
	@printf "Installing $(PROJECTNAME)...\n"
	@mkdir -p $(INSTALLDIR)
	@cp -a . $(INSTALLDIR)
	@cd $(INSTALLDIR) && composer update
	@chown -R nobody. $(INSTALLDIR)
	@echo -e "[Unit]\nDescription=IM Notifications Server\nAfter=network.target\n\n[Service]\nPIDFile=/var/run/imserver.pid\nExecStart=/usr/bin/php $(INSTALLDIR)/imserver start\nExecReload=/usr/bin/php $(INSTALLDIR)/imserver reload\nExecRestart=/usr/bin/php $(INSTALLDIR)/imserver restart\nExecStop=/usr/bin/php $(INSTALLDIR)/imserver stop\nWorkingDirectory=$(INSTALLDIR)\nRestart=always\nRestartSec=3\nStandardOutput=syslog\nStandardError=syslog\nSyslogIdentifier=imserver\nUser=nobody\nGroup=nobody\nLimitNOFILE=102400\nLimitNPROC=102400\nPrivateTmp=false\n\n[Install]\nWantedBy=multi-user.target\n" > /etc/systemd/system/imserver.service
	@systemctl daemon-reload && systemctl enable imserver
	@printf "Starting service...\n"
	@systemctl start imserver
	@printf "Installation Done.\n"
	@systemctl status imserver

uninstall:
	@printf "Uninstalling $(PROJECTNAME) service...\n"
	@systemctl stop imserver
	@systemctl disable imserver
	@rm -rf $(INSTALLDIR)
	@rm -f /etc/systemd/system/imserver.service
	@systemctl daemon-reload
	@printf "Uninstall Done.\n"

update: uninstall install

.PHONY: install uninstall update
