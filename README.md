# qrcode

Vytváří QR code obrázky dle zadaných parametrů v url. Parametry jsou stejné jako u [Google chart](https://developers.google.com/chart/infographics/docs/qr_codes), 
s drobnými rozdíly.

Vrací vždy **PNG** obrázek. Pokud se nepodaří vygenerovat dle zadaných parametrů, pokusí se vytvořit QR code s **low** 
correction levelem, **marginem 0** a **kódováním UTF-8**. Pokud ani to nelze, vrací bílý prázdný obrázek. 

## Parametry

### chs=\<width>x\<height>

Rozměry obrázku. Pokud nejsou zadané, defaultní jsou **150x150**. Pokud je zadaná jenom `width`, `height` bude stejná.
QR kódy jsou vždy čtvercové, pokud jsou `width` a `height` rozdílné, zbytek většího rozměru doplní bílým místem.

### chl=\<data>

Povinný parametr, data co jsou v QR kódu. Data musí být **urlencoded**!

### choe=\<output_encoding>

Kódování dat, defaultně **UTF-8**, jediná další možnost je **ISO-8859-1**. 
Pokud je zadáno **ISO-8859-1** a data obsahují znaky mimo tuto sadu, bude výsledný obrázek kódován **UTF-8**! 

### chld=\<error_correction_level>|\<margin>

Obsahuje 2 parametry, **error correction level** a **margin**.

**error correction level** může být:
- **L** = low - toto je **defaultní** nastavení
- **M** = medium
- **Q** = quartile
- **H** = high

**margin** šíře okraje, **defaultní hodnota = 4**. Stanovuje minimální margin, výsledná šíře záleží na rozměrech a nastavení error correction levelu.

## Příklad

http://{server}/?chl=https%3A%2F%2Fwww.cestovnisystem.cz%2F&chs=180x180&choe=ISO-8859-1&chld=H|0