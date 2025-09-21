# ETL Pipeline Example

Building a modular Extract, Transform, Load (ETL) pipeline using the Modular Framework.

## Overview

This example demonstrates how to build a data processing pipeline with clear separation between extraction, transformation, and loading phases. Each phase is a separate module with explicit boundaries.

## Architecture

```
ExtractModule → TransformModule → LoadModule
     ↓               ↓                ↓
Data Sources    Transformers      Data Sinks
```

## Implementation

### 1. Extract Module

```php
<?php

declare(strict_types=1);

namespace App\ETL;

use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\PowerModule\Contract\PowerModule;

class ExtractModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [
            DatabaseExtractor::class,
            CsvExtractor::class,
            ApiExtractor::class,
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Private connection management
        $container->set(ConnectionPool::class, ConnectionPool::class);
        $container->set(HttpClient::class, HttpClient::class);
        
        // Exported extractors
        $container->set(DatabaseExtractor::class, DatabaseExtractor::class)
            ->addArguments([ConnectionPool::class]);
            
        $container->set(CsvExtractor::class, CsvExtractor::class);
        
        $container->set(ApiExtractor::class, ApiExtractor::class)
            ->addArguments([HttpClient::class]);
    }
}

class DatabaseExtractor
{
    public function __construct(private ConnectionPool $pool) {}

    public function extract(string $query): iterable
    {
        $connection = $this->pool->getConnection();
        return $connection->query($query)->fetchAll();
    }
}

class CsvExtractor
{
    public function extract(string $filePath): iterable
    {
        $handle = fopen($filePath, 'r');
        
        while (($row = fgetcsv($handle)) !== false) {
            yield $row;
        }
        
        fclose($handle);
    }
}
```

### 2. Transform Module

```php
<?php

declare(strict_types=1);

namespace App\ETL;

use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\PowerModule\Contract\ImportsComponents;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\ImportItem;

class TransformModule implements PowerModule, ImportsComponents, ExportsComponents
{
    public static function imports(): array
    {
        return [
            ImportItem::create(ExtractModule::class, DatabaseExtractor::class, CsvExtractor::class),
        ];
    }

    public static function exports(): array
    {
        return [
            DataPipeline::class,
            TransformationEngine::class,
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Private transformation components
        $container->set(DataValidator::class, DataValidator::class);
        $container->set(DataCleaner::class, DataCleaner::class);
        $container->set(DataNormalizer::class, DataNormalizer::class);
        
        // Exported transformation engine
        $container->set(TransformationEngine::class, TransformationEngine::class)
            ->addArguments([DataValidator::class, DataCleaner::class, DataNormalizer::class]);
            
        // High-level pipeline orchestrator
        $container->set(DataPipeline::class, DataPipeline::class)
            ->addArguments([DatabaseExtractor::class, CsvExtractor::class, TransformationEngine::class]);
    }
}

class TransformationEngine
{
    public function __construct(
        private DataValidator $validator,
        private DataCleaner $cleaner,
        private DataNormalizer $normalizer,
    ) {}

    public function transform(iterable $data): iterable
    {
        foreach ($data as $record) {
            // Validate
            if (!$this->validator->isValid($record)) {
                continue; // Skip invalid records
            }
            
            // Clean
            $record = $this->cleaner->clean($record);
            
            // Normalize
            $record = $this->normalizer->normalize($record);
            
            yield $record;
        }
    }
}

class DataPipeline
{
    public function __construct(
        private DatabaseExtractor $dbExtractor,
        private CsvExtractor $csvExtractor,
        private TransformationEngine $transformer,
    ) {}

    public function processDatabase(string $query): iterable
    {
        $rawData = $this->dbExtractor->extract($query);
        return $this->transformer->transform($rawData);
    }

    public function processCsv(string $filePath): iterable
    {
        $rawData = $this->csvExtractor->extract($filePath);
        return $this->transformer->transform($rawData);
    }
}
```

### 3. Load Module

```php
<?php

declare(strict_types=1);

namespace App\ETL;

use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\ImportsComponents;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\ImportItem;

class LoadModule implements PowerModule, ImportsComponents
{
    public static function imports(): array
    {
        return [
            ImportItem::create(TransformModule::class, DataPipeline::class),
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Private loading infrastructure
        $container->set(DatabaseWriter::class, DatabaseWriter::class);
        $container->set(FileWriter::class, FileWriter::class);
        $container->set(ApiWriter::class, ApiWriter::class);
        
        // ETL orchestrator
        $container->set(EtlRunner::class, EtlRunner::class)
            ->addArguments([
                DataPipeline::class,
                DatabaseWriter::class,
                FileWriter::class,
                ApiWriter::class,
            ]);
    }
}

class EtlRunner
{
    public function __construct(
        private DataPipeline $pipeline,
        private DatabaseWriter $dbWriter,
        private FileWriter $fileWriter,
        private ApiWriter $apiWriter,
    ) {}

    public function runDatabaseToDatabase(string $sourceQuery, string $targetTable): void
    {
        $transformedData = $this->pipeline->processDatabase($sourceQuery);
        $this->dbWriter->write($targetTable, $transformedData);
    }

    public function runCsvToApi(string $csvPath, string $apiEndpoint): void
    {
        $transformedData = $this->pipeline->processCsv($csvPath);
        $this->apiWriter->write($apiEndpoint, $transformedData);
    }

    public function runDatabaseToFile(string $sourceQuery, string $outputPath): void
    {
        $transformedData = $this->pipeline->processDatabase($sourceQuery);
        $this->fileWriter->write($outputPath, $transformedData);
    }
}
```

## Usage

### Basic ETL Job

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\ETL\ExtractModule;
use App\ETL\TransformModule;
use App\ETL\LoadModule;
use App\ETL\EtlRunner;
use Modular\Framework\App\ModularAppBuilder;

// Build the ETL application
$app = new ModularAppBuilder(__DIR__)
    ->withModules(
        ExtractModule::class,
        TransformModule::class,
        LoadModule::class,
    )
    ->build();

// Run ETL jobs
$etlRunner = $app->get(EtlRunner::class);

// Transfer data from legacy database to new system
$etlRunner->runDatabaseToDatabase(
    'SELECT * FROM legacy_users WHERE created_at > ?',
    'new_users'
);

// Process CSV files from external system
$etlRunner->runCsvToApi(
    '/data/external_orders.csv',
    'https://api.internal.com/orders'
);

// Generate reports
$etlRunner->runDatabaseToFile(
    'SELECT department, COUNT(*) as employee_count FROM employees GROUP BY department',
    '/reports/department_summary.json'
);
```

### CLI Interface

```php
#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$command = $argv[1] ?? '';
$source = $argv[2] ?? '';
$target = $argv[3] ?? '';

$app = new ModularAppBuilder(__DIR__)
    ->withModules(ExtractModule::class, TransformModule::class, LoadModule::class)
    ->build();

$etlRunner = $app->get(EtlRunner::class);

match ($command) {
    'db-to-db' => $etlRunner->runDatabaseToDatabase($source, $target),
    'csv-to-api' => $etlRunner->runCsvToApi($source, $target),
    'db-to-file' => $etlRunner->runDatabaseToFile($source, $target),
    default => echo "Usage: etl.php {db-to-db|csv-to-api|db-to-file} <source> <target>\n",
};
```

## Benefits

### **Clear Separation of Concerns**
- Extract module focuses only on data sources
- Transform module handles all data processing logic
- Load module manages output destinations

### **Easy Testing**
```php
class TransformModuleTest extends TestCase
{
    public function testDataTransformation()
    {
        // Test transform module with mock extractors
        $app = new ModularAppBuilder(__DIR__)
            ->withModules(
                MockExtractModule::class, // Mock data sources
                TransformModule::class,   // Real transformation logic
            )
            ->build();

        $pipeline = $app->get(DataPipeline::class);
        // Test transformation rules
    }
}
```

### **Flexible Configuration**
- Swap extractors for different data sources
- Add new transformation rules without changing other modules
- Support multiple output formats

### **Scalability**
- Each module can be optimized independently
- Easy to add new data sources and destinations
- Clear path to microservice extraction

## Extensions

This ETL pipeline can be extended with additional modules:

- **MonitoringModule**: Track job progress, errors, and performance
- **SchedulingModule**: Cron-like job scheduling
- **ValidationModule**: Advanced data quality checks
- **ConfigModule**: External configuration for connections and rules