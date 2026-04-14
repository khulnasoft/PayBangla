#!/bin/bash

# Download WordPress test suite

# Get the latest version
WP_VERSION=$(curl -s https://api.wordpress.org/core/version-check/1.7/ | grep -oP '"version": "\K[0-9]+\.[0-9]+')

# Download WordPress core
curl -o wordpress.tar.gz "https://wordpress.org/wordpress-${WP_VERSION}.tar.gz"

# Extract WordPress
tar -xzf wordpress.tar.gz

# Move to tests directory
mv wordpress/* vendor/wp-phpunit/wp-phpunit/src/

# Clean up
rm wordpress.tar.gz