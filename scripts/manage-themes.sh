#!/bin/bash

# Define the WordPress themes directory
THEMES_DIR="wp-content/themes"

# Ensure the themes directory exists
if [ ! -d "$THEMES_DIR" ]; then
    echo "Error: Themes directory not found at $THEMES_DIR"
    exit 1
fi

# List available themes
echo "Available themes:"
THEMES=($(ls -d $THEMES_DIR/*/ | xargs -n 1 basename))

for i in "${!THEMES[@]}"; do
    echo "[$i] ${THEMES[$i]}"
done

# Ask user to select a theme
read -p "Enter the number of the theme you want to keep: " THEME_INDEX
SELECTED_THEME="${THEMES[$THEME_INDEX]}"

if [ -z "$SELECTED_THEME" ]; then
    echo "Invalid selection. Exiting."
    exit 1
fi

echo "Keeping theme: $SELECTED_THEME"

# Ensure twentytwentyone is kept by default
DEFAULT_THEMES=("twentytwentyone")

# Remove all other themes except the selected theme and twentytwentyone
for THEME in "${THEMES[@]}"; do
    if [[ "$THEME" != "$SELECTED_THEME" && ! " ${DEFAULT_THEMES[@]} " =~ " ${THEME} " ]]; then
        echo "Removing theme: $THEME"
        rm -rf "$THEMES_DIR/$THEME"
    fi
done

echo "Theme cleanup complete."

# Ask user if they want to generate a child theme
read -p "Do you want to create a child theme for $SELECTED_THEME? (y/n): " CREATE_CHILD_THEME

if [[ "$CREATE_CHILD_THEME" =~ ^[Yy]$ ]]; then
    php generator.php --name "${SELECTED_THEME}-child" --display-name "${SELECTED_THEME^} Child" --path "$THEMES_DIR" --parent "$SELECTED_THEME"
    echo "Child theme created."
fi

echo "Theme setup complete."
