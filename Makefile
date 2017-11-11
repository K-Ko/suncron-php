INSTALL_DIR ?= /usr/bin

# Some defaults
BIN_FILE = ${INSTALL_DIR}/suncron-php
CFG_DIR = /etc/suncron-php
CFG = ${CFG_DIR}/daylight.yaml

.PHONY: default install uninstall 

.DEFAULT:
	@echo "Usage: make install|uninstall"

default:
	@echo "Usage: make install|uninstall"

install:
	@echo "::: Install ..."
	@echo 

	ln -sf ${CURDIR}/suncron.php ${BIN_FILE}

	@install -d ${CFG_DIR}
	install -m644 ${CURDIR}/dist/* ${CFG_DIR}/

	@echo
	@echo "::: Done"

uninstall:
	@echo "::: Uninstall ..."
	@echo

	rm -rf ${CFG_DIR} ${BIN_FILE}

	@echo
	@echo "::: Done"

