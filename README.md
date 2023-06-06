# WordPress Utils

[![License: LGPL v3](https://img.shields.io/badge/License-GPL_v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![Packagist](https://img.shields.io/packagist/v/wedevs/wp-utils.svg)](https://packagist.org/packages/wedevs/wp-utils)
![GitHub all releases](https://img.shields.io/github/downloads/weDevsOfficial/wp-utils/total?label=GitHub%20Downloads)
![Packagist Downloads](https://img.shields.io/packagist/dt/wedevs/wp-utils?label=Packagist)

A collection of useful codes and utilities for WordPress plugin development. These simplifies common tasks and promote code reusability in WordPress projects.

## Installation

Install the package via Composer:

```bash
composer require wedevs/wp-utils
```

## Usage

### ContainerTrait

With `ContainerTrait` you can easily store and retrieve dynamic properties within your classes, providing a flexible and convenient way to manage and access data without the need for explicitly defined class properties.

Usage example:

```php
use WeDevs\WpUtils\ContainerTrait;

class MyPlugin {

    use ContainerTrait;

    public function __construct() {
        // Register an instance
        $this->my_service = new MyService();

        // Use the instance
        $this->my_service->doSomething();
    }

    // Rest of your plugin code...
}

```

### HookTrait

The `HookTrait` provides reusable methods for managing WordPress action and filter hooks in your plugin. It simplifies the process of adding, removing, or executing hooks.

```php

use WeDevs\WpUtils\HookTrait;

class MyPlugin {

    use HookTrait;

    public function __construct() {
        // Add an action hook
        $this->add_action( 'init', 'my_init_function' );

        // Add a filter hook
        $this->add_filter( 'the_title', 'my_title_filter' );
    }

    public function my_init_function() {
        // Actions to be performed during 'init'
    }

    public function my_title_filter( $title ) {
        // Modify the post title
        return $title . ' - Customized';
    }

    // Rest of your plugin code...
}

```

### LogTrait

The `LogTrait` provides a simple logging mechanism for your WordPress plugin. It allows you to log messages to the PHP error log.

Usage example:

```php
use WeDevs\WpUtils\LogTrait;

class MyPlugin {

    use LogTrait;

    public function some_method() {
        // Log an informational message
        $this->log_info( 'Some informational message.' );

        // Log an error message
        $this->log_error( 'An error occurred.' );

        // Log a debug message
        $this->log_debug( 'A debug message' );
    }

    // Rest of your plugin code...
}

```

### SingletonTrait

The `SingletonTrait` implements the singleton pattern for your WordPress plugin classes. It ensures that only one instance of the class is created and provides a global access point to that instance.

Usage example:

```php
use WeDevs\WpUtils\SingletonTrait;

class MySingletonClass {

    use SingletonTrait;

    // Rest of your singleton class implementation...
}

// Get the instance of the singleton class
$instance = MySingletonClass::instance();

// Use the instance
$instance->doSomething();

```


## License

This project is licensed under the GPL 2.0 or Later License.
