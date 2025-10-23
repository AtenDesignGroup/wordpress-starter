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

# Basic validation for new theme name
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

# Update style.css metadata
STYLE_CSS="$NEW_THEME_DIR/style.css"
if [ -f "$STYLE_CSS" ]; then
    if grep -q "^Theme Name:" "$STYLE_CSS"; then
        sed -i.bak "s/^Theme Name:.*/Theme Name: $NEW_THEME_NAME/" "$STYLE_CSS"
    else
        echo "Theme Name: $NEW_THEME_NAME" >> "$STYLE_CSS"
    fi

    if grep -q "^Text Domain:" "$STYLE_CSS"; then
        sed -i.bak "s/^Text Domain:.*/Text Domain: $NEW_THEME_NAME/" "$STYLE_CSS"
    else
        echo "Text Domain: $NEW_THEME_NAME" >> "$STYLE_CSS"
    fi

    rm "$STYLE_CSS.bak"
fi

# Replace --aten with --new-theme-name in select files
TARGET_FILES=(
    "$NEW_THEME_DIR/libraries/global/00-base/_colors.scss"
    "$NEW_THEME_DIR/libraries/global/00-base/_typography.scss"
    "$NEW_THEME_DIR/editor-style.scss"
)

for FILE in "${TARGET_FILES[@]}"; do
    if [ -f "$FILE" ]; then
        sed -i.bak "s/--aten/--$NEW_THEME_NAME/g" "$FILE"
        rm "$FILE.bak"
        echo "🔧 Updated CSS vars in: $FILE"
    else
        echo "⚠️ File not found, skipping: $FILE"
    fi
done

echo "🎉 Theme copied and customized to '$NEW_THEME_NAME' successfully!"
echo "👉 You can now activate it in WordPress admin."


#loop through blocks and replace aten with new theme name
#pass directory of blocks and theming directory
#run npm install
#run npm run build
#remove node modules.
# Define block and theming directories
BLOCKS_DIR="$NEW_THEME_DIR/blocks"
THEMING_DIR="$NEW_THEME_DIR/libraries"

# Replace "aten", "aten_fse", or "aten_hybrid" with new theme name
if [ -d "$BLOCKS_DIR" ]; then
    echo "🔄 Updating block references (aten, aten_fse, aten_hybrid → $NEW_THEME_NAME)..."
    find "$BLOCKS_DIR" "$THEMING_DIR" -type f -exec sed -i.bak -E \
        "s/aten(_fse|_hybrid)?/$NEW_THEME_NAME/g" {} +
    find "$BLOCKS_DIR" "$THEMING_DIR" -type f -name "*.bak" -delete
    echo "✅ Block references updated."
else
    echo "⚠️ No blocks directory found at $BLOCKS_DIR, skipping replacement."
fi


# Run npm commands if package.json exists
if [ -f "$NEW_THEME_DIR/package.json" ]; then
    echo "📦 Installing npm dependencies..."
    (cd "$NEW_THEME_DIR" && npm install)

    echo "⚙️ Running npm build..."
    (cd "$NEW_THEME_DIR" && npm run build)

    echo "🧹 Cleaning up node_modules..."
    rm -rf "$NEW_THEME_DIR/node_modules"

    echo "✅ npm build complete."
else
    echo "⚠️ No package.json found in $NEW_THEME_DIR, skipping npm build."
fi

echo "🎉 Theme copied and customized to '$NEW_THEME_NAME' successfully!"
echo "👉 You can now activate it in WordPress admin."
