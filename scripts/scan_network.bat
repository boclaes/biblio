@echo off
REM Scan the network and attempt to find hostname
FOR /L %%i IN (1,1,254) DO (
    ping -n 1 -w 100 192.168.1.%%i > NUL
    nslookup 192.168.1.%%i
)
arp -a
