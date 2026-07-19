#!/bin/bash

# Script to install Xdebug for PHP 8.0
# This script will help you install Xdebug, which is required for generating code coverage reports

echo "Installing Xdebug for PHP 8.0..."
echo "--------------------------------"

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed or not in PATH"
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "Detected PHP version: $PHP_VERSION"

if [[ ! "$PHP_VERSION" =~ ^8\.0 ]]; then
    echo "Warning: This script is designed for PHP 8.0.x, but you have $PHP_VERSION"
    echo "The installation might not work correctly."
    read -p "Do you want to continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Detect OS
if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    OS="Linux"
    # Detect Linux distribution
    if [ -f /etc/debian_version ]; then
        DISTRO="Debian"
        echo "Detected Debian-based distribution"
        echo "Installing dependencies..."
        sudo apt-get update
        sudo apt-get install -y php8.0-dev autoconf automake make gcc
        
        echo "Installing Xdebug via PECL..."
        sudo pecl install xdebug
        
        # Find the PHP configuration directories
        PHP_CONF_DIR=$(php -i | grep "Scan this dir for additional .ini files" | awk '{print $NF}')
        PHP_INI_DIR=$(php -i | grep "Loaded Configuration File" | awk '{print $NF}' | xargs dirname)
        
        echo "Creating Xdebug configuration..."
        echo "zend_extension=xdebug.so
xdebug.mode=coverage,develop,debug
xdebug.start_with_request=yes
xdebug.client_port=9003
xdebug.client_host=127.0.0.1" | sudo tee $PHP_CONF_DIR/20-xdebug.ini
        
    elif [ -f /etc/redhat-release ]; then
        DISTRO="RedHat"
        echo "Detected RedHat-based distribution"
        echo "Installing dependencies..."
        sudo yum install -y php-devel php-pear gcc make
        
        echo "Installing Xdebug via PECL..."
        sudo pecl install xdebug
        
        # Find the PHP configuration directories
        PHP_CONF_DIR=$(php -i | grep "Scan this dir for additional .ini files" | awk '{print $NF}')
        PHP_INI_DIR=$(php -i | grep "Loaded Configuration File" | awk '{print $NF}' | xargs dirname)
        
        echo "Creating Xdebug configuration..."
        echo "zend_extension=xdebug.so
xdebug.mode=coverage,develop,debug
xdebug.start_with_request=yes
xdebug.client_port=9003
xdebug.client_host=127.0.0.1" | sudo tee $PHP_CONF_DIR/20-xdebug.ini
        
    else
        echo "Unsupported Linux distribution. Please install Xdebug manually."
        exit 1
    fi
    
elif [[ "$OSTYPE" == "darwin"* ]]; then
    OS="macOS"
    echo "Detected macOS"
    echo "Installing Xdebug via PECL..."
    
    # Check if Homebrew is installed
    if command -v brew &> /dev/null; then
        echo "Homebrew is installed. Installing dependencies..."
        brew install autoconf automake
    else
        echo "Homebrew is not installed. Please install it first: https://brew.sh/"
        exit 1
    fi
    
    pecl install xdebug
    
    # Find the PHP configuration directories
    PHP_CONF_DIR=$(php -i | grep "Scan this dir for additional .ini files" | awk '{print $NF}')
    PHP_INI_DIR=$(php -i | grep "Loaded Configuration File" | awk '{print $NF}' | xargs dirname)
    
    echo "Creating Xdebug configuration..."
    echo "zend_extension=xdebug.so
xdebug.mode=coverage,develop,debug
xdebug.start_with_request=yes
xdebug.client_port=9003
xdebug.client_host=127.0.0.1" | sudo tee $PHP_CONF_DIR/20-xdebug.ini
    
elif [[ "$OSTYPE" == "cygwin" ]] || [[ "$OSTYPE" == "msys" ]] || [[ "$OSTYPE" == "win32" ]]; then
    OS="Windows"
    echo "Detected Windows"
    echo "For Windows, please follow these manual installation steps:"
    echo "1. Download the appropriate Xdebug DLL from https://xdebug.org/download"
    echo "2. Place the DLL in your PHP extension directory"
    echo "3. Add the following to your php.ini file:"
    echo "   [xdebug]"
    echo "   zend_extension=xdebug.dll"
    echo "   xdebug.mode=coverage,develop,debug"
    echo "   xdebug.start_with_request=yes"
    echo "   xdebug.client_port=9003"
    echo "   xdebug.client_host=127.0.0.1"
    echo "4. Restart your web server"
    exit 0
else
    echo "Unsupported operating system: $OSTYPE"
    exit 1
fi

# Verify installation
echo "Verifying Xdebug installation..."
php -m | grep -i xdebug
if [ $? -eq 0 ]; then
    echo "Xdebug has been successfully installed!"
    echo "You can now generate code coverage reports using:"
    echo "./generate-coverage.sh"
    echo "or"
    echo "composer coverage"
else
    echo "Xdebug installation failed or Xdebug is not properly configured."
    echo "Please check the error messages above and try to install Xdebug manually."
fi

echo "--------------------------------"
echo "For more information about Xdebug, visit: https://xdebug.org/docs/"