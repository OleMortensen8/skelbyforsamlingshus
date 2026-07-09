#!/bin/bash

# Ensure the thumbnails directory exists
mkdir -p assets/img/skelby/inspiration/thumbnails

# Process each image in the source directory
for img in assets/img/skelby/inspiration/*.{jpg,png,jpeg}; do
    if [ -f "$img" ]; then
        # Extract the filename without the directory
        filename=$(basename "$img")
        
        # Generate the thumbnail with a 'thumbnail_' prefix
        magick "$img" -thumbnail 324x243 "assets/img/skelby/inspiration/thumbnails/thumbnail_${filename}"
    else
        echo "No image files found matching $img"
    fi
done

# Ensure the thumbnails directory exists
mkdir -p assets/img/skelby/gamlebilleder/thumbnails

# Process each image in the source directory
for img in assets/img/skelby/gamlebilleder/*.{jpg,png,jpeg}; do
    if [ -f "$img" ]; then
        # Extract the filename without the directory
        filename=$(basename "$img")

        # Generate the thumbnail with a 'thumbnail_' prefix
        magick "$img" -thumbnail 324x243 "assets/img/skelby/gamlebilleder/thumbnails/thumbnail_${filename}"
    else
        echo "No image files found matching $img"
    fi
done
