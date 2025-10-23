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
        echo "⚠️  Warning: WordPress configuration detected in $INSTALL_DIR."
        echo "This usually means a StarterKit or existing install is present."
        read -p "Do you want to refresh WordPress core files and continue? (y/n): " CONFIRM

        if [[ "$CONFIRM" =~ ^[Yy]$ ]]; then
            echo ""
            echo "🧹 Removing existing WordPress core files but keeping StarterKit and wp-config.php..."
            echo "👉 Note: Your StarterKit configuration and custom files will remain untouched."
            echo ""

            # Remove only WordPress core folders and top-level PHP files (but NOT wp-config.php)
            rm -rf "$INSTALL_DIR/wp-admin" \
                   "$INSTALL_DIR/wp-includes" \
                   "$INSTALL_DIR/wp-content/index.php" \
                   "$INSTALL_DIR/license.txt" \
                   "$INSTALL_DIR/readme.html" \
                   "$INSTALL_DIR/wp-activate.php" \
                   "$INSTALL_DIR/wp-blog-header.php" \
                   "$INSTALL_DIR/wp-comments-post.php" \
                   "$INSTALL_DIR/wp-config-sample.php" \
                   "$INSTALL_DIR/wp-cron.php" \
                   "$INSTALL_DIR/wp-links-opml.php" \
                   "$INSTALL_DIR/wp-load.php" \
                   "$INSTALL_DIR/wp-login.php" \
                   "$INSTALL_DIR/wp-mail.php" \
                   "$INSTALL_DIR/wp-settings.php" \
                   "$INSTALL_DIR/wp-signup.php" \
                   "$INSTALL_DIR/wp-trackback.php" \
                   "$INSTALL_DIR/xmlrpc.php"

            echo "✅ Core files removed. Proceeding with fresh WordPress core installation..."
        else
            echo "Aborting setup."
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
