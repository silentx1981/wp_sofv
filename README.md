# Wordpress SOFV Integration

# Requirements
* PHP 7.4 or higher
  * DOMDocument
  * tidy
* Wordpress 5.6 or higher

# Requirements Development
* Node.js 14 or higher
* npm 6.14 or higher
* Composer 2 or higher

# Usage Spiele
[sofvGames url="..." type="..." resultMode="..."]

* url = URL der SOFV Seite
* type = all/team/current
  * all = Vereinsspielplan 
  * team = Team-Spielplan
  * current = Aktuelle Spiele Seite
* resultMode = renderCarousel/renderGrid
  * renderCarousel = 3 Spalten Slider (pro Tag)
  * renderGrid = 3 Spalten / 1-n Zeilen
    
# usage Rangliste
[sofvRanking url="..."]

* url = URL der SOFV Seite Resultate + Rangliste

# Create Plugin
1. Checkout
2. Lösche folgende Ordner: bin/scss
3. Lösche folgende Dateien: .gitignore, composer.json, package-lock.json
4. Zip Ordner: wp_sofv -> wp.sofv.zip
5. Im Wordpress unter Plugins hochladen
