INSTALL_DIR ?= /usr/bin

.PHONY: default install uninstall 

.DEFAULT:
	@echo "Usage: make install|uninstall"

default:
	@echo "make install|uninstall"

install:
	@echo "Installing ..."
	@echo

	ln -s ${CURDIR}/suncron.php ${INSTALL_DIR}/suncron

	@install -d /etc/default
	install -m644 dist/suncron*.yaml /etc/default/

	@sed < dist/suncron.sh >/tmp/suncron.sh -e "s#SUNCRON_PATH#${INSTALL_DIR}#"
	@install -m644 /tmp/suncron.sh /etc/cron.d/suncron-daily
	@rm -f /tmp/suncron.sh

	@echo
	@echo "Installed a daily run of /etc/default/suncron.yaml"
	@echo "Edit /etc/default/suncron.yaml or /etc/cron.d/suncron-daily for your needs"
	@echo

	@echo "Done."

uninstall:
	@echo "Uninstalling ..."
	@echo

	rm -f /etc/cron.d/suncron-daily
	rm -f ${INSTALL_DIR}/suncron
	rm -f /etc/default/suncron*.yaml

	@echo
	@echo "Done"

