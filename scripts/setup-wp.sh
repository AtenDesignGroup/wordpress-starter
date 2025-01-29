#!/bin/bash

# WordPress Installation directory (root of the project)
INSTALL_DIR="."

# Step 1: Install WordPress core (if not already installed)
bash scripts/install-wp.sh --download

# Step 2: Check if WordPress is installed in the root
if [ ! -f "$INSTALL_DIR/wp-config.php" ]; then
    echo "WordPress configuration file not found, running wp core download..."
    # Ensure wp-cli is available (or provide full path to wp-cli)
    wp core download --path=$INSTALL_DIR
fi

# Step 3: Manage themes (script is for handling theme selection)
bash scripts/manage-themes.sh

echo "WordPress install complete!"
