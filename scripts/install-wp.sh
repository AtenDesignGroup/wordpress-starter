#!/bin/bash

# Default values
DEFAULT_WP_VERSION="latest"
INSTALL_DIR="."  # Install WordPress in project root
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

    # Warn user if files already exist
    if [ -f "$INSTALL_DIR/wp-config.php" ]; then
        echo "Warning: WordPress already appears to be installed in $INSTALL_DIR."
        read -p "Do you want to remove the existing installation and continue? (y/n): " CONFIRM
        if [[ "$CONFIRM" =~ ^[Yy]$ ]]; then
            echo "Removing existing WordPress files..."
            rm -rf "$INSTALL_DIR"/*
        else
            echo "Aborting."
            exit 1
        fi
    fi

    # Download and extract WordPress
    echo "Downloading WordPress version $WP_VERSION..."
    curl -o wordpress.tar.gz -L "$WP_URL"
    mkdir -p "$INSTALL_DIR"
    tar -xzf wordpress.tar.gz --strip-components=1 -C "$INSTALL_DIR"
    rm wordpress.tar.gz

    echo "WordPress $WP_VERSION installed successfully in $INSTALL_DIR!"
else
    echo "Skipping WordPress core download..."
fi
