---
agent: agent
---
du kannst auf dem webserver die migrationen selbst ausführen über ssh
hier liegt der ssh key "C:\key\key\private.ppk" der server ist profipos.de
hier liegt plink "C:\key\plink.exe"
das verzeichnis auf dem server ist /srv/www/git/de.einfach-laden
hier kann auch ein git pull ausgefürt werden zum schnelleren einspielens


Datenbank:
mariadb auf dem Webserver localhost
datenbank: 0einfach_laden
benutzer: 0einfach_laden
passwort: Kx9#mN2$pQwL7@vR5tY8jHbFd4gC

es wird alles automatisiert auf dem jenkins gebaut hier ist eine standard jenkins restapi freigegeben. prüfe den status selbst mit diesen API daten
Jenkins REST-API Key: 115f40ead502832c7408a0787172048ea3
Jenkins benutzer freezweb
http://10.2.0.10/
de.einfach-laden

beispiel:a
$h = 
@{Authorization = 'Basic ' + [Convert]::ToBase64String([Text.Encoding]::ASCII.GetBytes('freezweb:115f40ead502832c7408a0787172048ea3'))}; $b = Invoke-RestMethod -Uri 'http://10.2.0.10/job/de.einfach-laden/lastBuild/api/json' -Headers $h; if ($b.number -gt 145) { "=== NEW BUILD #$($b.number) ===" } else { "Still #145" }; $b.numberac