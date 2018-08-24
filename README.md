IM Notifications Server
===

[im-server]

Currently supported IM`s
==
1. Telegram (with proxy option)
2. ICQ

Usage
==

Installation
```
git clone <this project>
cd <this project>
make install
cp /data/im-server/config/config.local.php.example /data/im-server/config/config.local.php
vi /data/im-server/config/config.local.php
systemctl restart imserver
```

Uninstall
```
make uninstall
```

Update
```
make update
```

Zabbix alert examples:

Telegram protocol
```
curl -s --max-time 10 -X POST "http://127.0.0.1:8181" -d proto="telegram" -d chat_id="-1111111111" -d message="test message" | grep -q "status: FAIL" && exit 1
```

ICQ Protocol
```
curl -s --max-time 10 -X POST "http://127.0.0.1:8181" -d proto="icq" -d uin="12345677" -d message="test message" | grep -q "status: FAIL" && exit 1
```

TODO
==

- Add Message Queue support
- Add multiple proxies support
- Add proxy support for all protocols
- Add Viber support
- Add Skype support
