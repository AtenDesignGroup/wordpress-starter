#!/bin/bash

# WordPress Installation directory (root of the project)
INSTALL_DIR="."
export INSTALL_DIR

# Step 1: Install WordPress core
bash scripts/install-wp.sh --download

# Step 2: Manage themes
bash scripts/manage-themes.sh

echo "WordPress install complete!"
