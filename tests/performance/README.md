# Performance Testing

This directory contains performance tests for the SkelbyForsamlinghus project using Apache JMeter.

## Requirements

To run the performance tests, you need to have the following installed:

- Java Runtime Environment (JRE) 8 or higher
- Apache JMeter 5.4 or higher

## Installation

1. Download Apache JMeter from the [official website](https://jmeter.apache.org/download_jmeter.cgi)
2. Extract the archive to a directory of your choice
3. Add the `bin` directory to your PATH environment variable

## Running Performance Tests

To run the performance tests, use the following command:

```bash
jmeter -n -t tests/performance/HomepageLoadTest.jmx -l results.jtl -e -o report
```

This will run the HomepageLoadTest.jmx test plan in non-GUI mode, save the results to results.jtl, and generate an HTML
report in the report directory.

## Test Plans

### HomepageLoadTest.jmx

This test plan simulates multiple users accessing the homepage simultaneously to test the performance of the homepage
under load.

### BookingProcessLoadTest.jmx

This test plan simulates multiple users going through the booking process simultaneously to test the performance of the
booking system under load.

## Performance Metrics

The performance tests measure the following metrics:

- Response time (average, median, 90th percentile, 95th percentile, 99th percentile)
- Throughput (requests per second)
- Error rate
- CPU usage
- Memory usage

## Performance Targets

The application should meet the following performance targets:

- Homepage response time: < 2 seconds for 95% of requests
- Booking process response time: < 5 seconds for 95% of requests
- Error rate: < 1% under load
- Throughput: > 10 requests per second for the homepage

## Continuous Integration

The performance tests are run as part of the CI/CD pipeline to ensure that new changes do not negatively impact
performance.