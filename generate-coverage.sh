#!/bin/bash

# This script generates code coverage reports for the SkelbyForsamlinghus project.
# It creates HTML, Clover XML, and text reports as configured in phpunit.xml.
#
# Usage:
#   ./generate-coverage.sh [options]
#
# Options:
#   --no-browser    Don't open the HTML report in a browser
#   --help          Display this help message
#
# The reports are generated in the following locations:
#   - HTML: tests/log/report/
#   - Clover XML: coverage.xml
#   - Text: Displayed in the console output

# Process command line arguments
OPEN_BROWSER=true
for arg in "$@"; do
    case $arg in
        --no-browser)
            OPEN_BROWSER=false
            shift
            ;;
        --help)
            echo "Usage: ./generate-coverage.sh [options]"
            echo ""
            echo "Options:"
            echo "  --no-browser    Don't open the HTML report in a browser"
            echo "  --help          Display this help message"
            echo ""
            echo "The reports are generated in the following locations:"
            echo "  - HTML: tests/log/report/"
            echo "  - Clover XML: coverage.xml"
            echo "  - Text: Displayed in the console output"
            exit 0
            ;;
    esac
done

# Create the log directory if it doesn't exist
mkdir -p tests/log/report

# Generate code coverage reports
echo "Generating code coverage reports..."
vendor/bin/phpunit

# Check if the reports were generated successfully
if [ $? -eq 0 ]; then
    echo "Code coverage reports generated successfully."
    echo ""
    echo "Report locations:"
    echo "  - HTML: $(pwd)/tests/log/report/index.html"
    echo "  - Clover XML: $(pwd)/coverage.xml"
    echo ""
    echo "Summary of code coverage:"
    echo "------------------------"
    if [ -f coverage.xml ]; then
        # Extract coverage percentage from the Clover XML report
        COVERAGE=$(grep -o 'percent="[0-9.]*"' coverage.xml | head -1 | cut -d'"' -f2)
        if [ -n "$COVERAGE" ]; then
            echo "Overall code coverage: $COVERAGE%"
        else
            echo "Could not extract coverage percentage from the Clover XML report."
        fi
    else
        echo "Clover XML report was not generated."
    fi
    echo "------------------------"
    echo ""

    # Open the HTML report in the default browser if requested
    if [ "$OPEN_BROWSER" = true ]; then
        echo "Opening HTML report in the default browser..."
        if [ "$(uname)" == "Darwin" ]; then
            # macOS
            open tests/log/report/index.html
        elif [ "$(expr substr $(uname -s) 1 5)" == "Linux" ]; then
            # Linux
            if [ -n "$BROWSER" ]; then
                $BROWSER tests/log/report/index.html
            elif [ -x "$(command -v xdg-open)" ]; then
                xdg-open tests/log/report/index.html
            elif [ -x "$(command -v gnome-open)" ]; then
                gnome-open tests/log/report/index.html
            else
                echo "Could not detect the web browser to use."
                echo "Please open the following file in your browser:"
                echo "$(pwd)/tests/log/report/index.html"
            fi
        elif [ "$(expr substr $(uname -s) 1 10)" == "MINGW32_NT" ] || [ "$(expr substr $(uname -s) 1 10)" == "MINGW64_NT" ]; then
            # Windows
            start tests/log/report/index.html
        else
            echo "Could not detect the operating system."
            echo "Please open the following file in your browser:"
            echo "$(pwd)/tests/log/report/index.html"
        fi
    fi
else
    echo "Failed to generate code coverage reports."
    exit 1
fi
