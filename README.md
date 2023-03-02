# wysiwyg* - Cookie Handling
 
![Neos Package](https://img.shields.io/badge/Neos-Package-blue.svg "Neos Package")
![Flow Package](https://img.shields.io/badge/Flow-Package-orange.svg "Flow Package")
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

![Neos Project](https://img.shields.io/badge/Neos-%20%3E=%208.0%20-blue.svg "Neos Project")
![PHP 7.1 and above](https://img.shields.io/badge/PHP-%20%3E=%208.1%20-blue.svg "PHP >= 7.1")
 
Neos component to safely deal with cookies and make their handling conform with European law (GDPR).   
This package provides simple and fast to use functions and a cookie layer. 

## Documentation

[Documentation](https://wysiwyg-software-design.github.io/Wysiwyg.CookieHandling)

## Installation
 
You can add this package via composer. Check the requirements and run the commands below.

#### Requirements:
- **PHP:** >= 8.1
- **Neos:** >= 8.0

#### Installation:
```bash
composer require wy/cookie-handling
```

#### Apply migrations:
```bash
./flow flow:doctrine:migrate
```

**Supported databases**
- MySQL
- PostgreSQL

## Contributing
Pull requests are welcome. For major changes please open an issue first to discuss what you would like to change.

## Authors
[Sven Wütherich](https://github.com/svwu)  
[Alexander Schulte](https://github.com/Alex-Schulte)  
[Eva-Maria Müller](https://github.com/emmue)  
[Marvin Kuhn](https://github.com/breadlesscode)

## License

This package is released under the MIT License (MIT). Please see the [License File](LICENSE) for more information.
