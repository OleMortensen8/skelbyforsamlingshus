#!/bin/bash

# Script to run performance tests for SkelbyForsamlinghus
# This script runs JMeter performance tests and checks if the performance targets are met

# Default values
HOST="localhost"
PORT="8080"
PROTOCOL="http"
OUTPUT_DIR="tests/performance/results"
REPORT_DIR="tests/performance/report"

# Parse command line arguments
while [[ $# -gt 0 ]]; do
  key="$1"
  case $key in
    --host)
      HOST="$2"
      shift
      shift
      ;;
    --port)
      PORT="$2"
      shift
      shift
      ;;
    --protocol)
      PROTOCOL="$2"
      shift
      shift
      ;;
    --output-dir)
      OUTPUT_DIR="$2"
      shift
      shift
      ;;
    --report-dir)
      REPORT_DIR="$2"
      shift
      shift
      ;;
    *)
      echo "Unknown option: $1"
      exit 1
      ;;
  esac
done

# Create output and report directories if they don't exist
mkdir -p "$OUTPUT_DIR"
mkdir -p "$REPORT_DIR"

# Check if JMeter is installed
if ! command -v jmeter &> /dev/null; then
  echo "Error: Apache JMeter is not installed or not in PATH"
  echo "Please install JMeter from https://jmeter.apache.org/download_jmeter.cgi"
  exit 1
fi

echo "Running performance tests with the following configuration:"
echo "Host: $HOST"
echo "Port: $PORT"
echo "Protocol: $PROTOCOL"
echo "Output directory: $OUTPUT_DIR"
echo "Report directory: $REPORT_DIR"

# Update JMeter test files with the provided host, port, and protocol
for test_file in tests/performance/*.jmx; do
  echo "Updating $test_file with server configuration..."
  # Use sed to update the server configuration in the JMeter test file
  sed -i "s/<stringProp name=\"HTTPSampler.domain\">.*<\/stringProp>/<stringProp name=\"HTTPSampler.domain\">$HOST<\/stringProp>/g" "$test_file"
  sed -i "s/<stringProp name=\"HTTPSampler.port\">.*<\/stringProp>/<stringProp name=\"HTTPSampler.port\">$PORT<\/stringProp>/g" "$test_file"
  sed -i "s/<stringProp name=\"HTTPSampler.protocol\">.*<\/stringProp>/<stringProp name=\"HTTPSampler.protocol\">$PROTOCOL<\/stringProp>/g" "$test_file"
done

# Run the HomepageLoadTest
echo "Running HomepageLoadTest..."
jmeter -n -t tests/performance/HomepageLoadTest.jmx -l "$OUTPUT_DIR/homepage_results.jtl" -e -o "$REPORT_DIR/homepage"

# Run the BookingProcessLoadTest
echo "Running BookingProcessLoadTest..."
jmeter -n -t tests/performance/BookingProcessLoadTest.jmx -l "$OUTPUT_DIR/booking_results.jtl" -e -o "$REPORT_DIR/booking"

# Check if the performance targets are met
echo "Checking performance targets..."

# Function to check if performance targets are met
check_performance_targets() {
  local results_file="$1"
  local test_name="$2"
  local response_time_target="$3"
  local error_rate_target="$4"
  local throughput_target="$5"
  
  # Extract metrics from the JMeter results file
  local avg_response_time=$(awk -F',' '{sum+=$1; count++} END {print sum/count}' "$results_file")
  local error_count=$(grep -c "false" "$results_file")
  local total_count=$(wc -l < "$results_file")
  local error_rate=$(echo "scale=4; $error_count / $total_count * 100" | bc)
  local throughput=$(echo "scale=2; $total_count / ($(tail -n1 "$results_file" | cut -d',' -f1) - $(head -n1 "$results_file" | cut -d',' -f1)) * 1000" | bc)
  
  echo "$test_name Results:"
  echo "  Average Response Time: ${avg_response_time}ms (Target: <${response_time_target}ms)"
  echo "  Error Rate: ${error_rate}% (Target: <${error_rate_target}%)"
  echo "  Throughput: ${throughput} requests/second (Target: >${throughput_target} requests/second)"
  
  # Check if targets are met
  local targets_met=true
  if (( $(echo "$avg_response_time > $response_time_target" | bc -l) )); then
    echo "  ❌ Response time target not met"
    targets_met=false
  fi
  
  if (( $(echo "$error_rate > $error_rate_target" | bc -l) )); then
    echo "  ❌ Error rate target not met"
    targets_met=false
  fi
  
  if (( $(echo "$throughput < $throughput_target" | bc -l) )); then
    echo "  ❌ Throughput target not met"
    targets_met=false
  fi
  
  if $targets_met; then
    echo "  ✅ All performance targets met for $test_name"
    return 0
  else
    echo "  ❌ Some performance targets not met for $test_name"
    return 1
  fi
}

# Check homepage performance targets
homepage_targets_met=$(check_performance_targets "$OUTPUT_DIR/homepage_results.jtl" "Homepage" 2000 1 10)
homepage_result=$?

# Check booking process performance targets
booking_targets_met=$(check_performance_targets "$OUTPUT_DIR/booking_results.jtl" "Booking Process" 5000 1 5)
booking_result=$?

# Overall result
if [ $homepage_result -eq 0 ] && [ $booking_result -eq 0 ]; then
  echo "✅ All performance tests passed!"
  exit 0
else
  echo "❌ Some performance tests failed. Check the reports for details."
  exit 1
fi