#!/bin/bash

THEMES_DIR="wp-content/themes"

if [ ! -d "$THEMES_DIR" ]; then
    echo "❌ Error: Themes directory not found at $THEMES_DIR"
    exit 1
fi

echo "📂 Available themes:"

THEMES=()
while IFS= read -r -d '' dir; do
    THEMES+=("$(basename "$dir")")
done < <(find "$THEMES_DIR" -mindepth 1 -maxdepth 1 -type d -print0)

if [ ${#THEMES[@]} -eq 0 ]; then
    echo "❌ No themes found in $THEMES_DIR"
    exit 1
fi

for i in "${!THEMES[@]}"; do
    echo "  [$i] ${THEMES[$i]}"
done

read -p "🎯 Enter the number of the theme you want to copy: " THEME_INDEX
SELECTED_THEME="${THEMES[$THEME_INDEX]}"

if [ -z "$SELECTED_THEME" ]; then
    echo "❌ Invalid selection. Exiting."
    exit 1
fi

echo "✅ Selected theme to copy: $SELECTED_THEME"

read -p "✏️ Enter the new theme name (machine name, no spaces): " NEW_THEME_NAME

# Basic validation for new theme name: no spaces, not empty
if [[ -z "$NEW_THEME_NAME" || "$NEW_THEME_NAME" =~ [^a-zA-Z0-9_-] ]]; then
    echo "❌ Invalid theme name. Use only letters, numbers, underscores, or hyphens."
    exit 1
fi

NEW_THEME_DIR="$THEMES_DIR/$NEW_THEME_NAME"

if [ -d "$NEW_THEME_DIR" ]; then
    echo "❌ Directory $NEW_THEME_DIR already exists. Choose a different name."
    exit 1
fi

# Copy the theme directory
cp -R "$THEMES_DIR/$SELECTED_THEME" "$NEW_THEME_DIR"

# Now update the copied theme’s style.css Theme Name and Text Domain
STYLE_CSS="$NEW_THEME_DIR/style.css"
if [ -f "$STYLE_CSS" ]; then
    # Update Theme Name line (replace or add)
    if grep -q "^Theme Name:" "$STYLE_CSS"; then
        sed -i.bak "s/^Theme Name:.*/Theme Name: $NEW_THEME_NAME/" "$STYLE_CSS"
    else
        echo "Theme Name: $NEW_THEME_NAME" >> "$STYLE_CSS"
    fi

    # Update Text Domain line similarly
    if grep -q "^Text Domain:" "$STYLE_CSS"; then
        sed -i.bak "s/^Text Domain:.*/Text Domain: $NEW_THEME_NAME/" "$STYLE_CSS"
    else
        echo "Text Domain: $NEW_THEME_NAME" >> "$STYLE_CSS"
    fi

    rm "$STYLE_CSS.bak"
fi

echo "🎉 Theme copied to '$NEW_THEME_NAME' successfully!"
echo "👉 You can now activate it in your WordPress admin."
