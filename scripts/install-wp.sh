#!/bin/bash

# Default values
DEFAULT_WP_VERSION="latest"
INSTALL_DIR=""
DOWNLOAD_CORE=false

# Parse command-line arguments
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --download) DOWNLOAD_CORE=true ;; # Enable WordPress core download
    esac
    shift
done

# Check if we should download WordPress core
if [ "$DOWNLOAD_CORE" = true ]; then
    # Prompt user for a WordPress version
    read -p "Enter WordPress version to download (default: $DEFAULT_WP_VERSION): " WP_VERSION
    WP_VERSION=${WP_VERSION:-$DEFAULT_WP_VERSION}

    # Define the download URL
    if [ "$WP_VERSION" == "latest" ]; then
        WP_URL="https://wordpress.org/latest.tar.gz"
    else
        WP_URL="https://wordpress.org/wordpress-${WP_VERSION}.tar.gz"
    fi

    # Ensure the install directory is clean
    if [ -d "$INSTALL_DIR" ]; then
        echo "Removing existing WordPress installation..."
        rm -rf "$INSTALL_DIR"
    fi

    # Create the directory
    mkdir -p "$INSTALL_DIR"

    # Download and extract WordPress
    echo "Downloading WordPress version $WP_VERSION..."
    curl -o wordpress.tar.gz -L $WP_URL
    tar -xzf wordpress.tar.gz --strip-components=1 -C "$INSTALL_DIR"
    rm wordpress.tar.gz

    echo "WordPress $WP_VERSION installed successfully in $INSTALL_DIR!"
else
    echo "Skipping WordPress core download..."
fi
