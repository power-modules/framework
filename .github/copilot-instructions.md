# Modular Framework - AI Coding Agent Instructions

This document provides guidance for AI coding agents to effectively contribute to the Modular Framework codebase.

## Big Picture Architecture

The Modular Framework is designed to build modular PHP applications. The core idea is that each module is a self-contained unit with its own Dependency Injection (DI) container, promoting encapsulation and clear boundaries.

- **Modules (`PowerModule`):** The fundamental building blocks of the framework. Each module has its own DI container and can register its own components. Key interface: `\Modular\Framework\PowerModule\Contract\PowerModule`.
- **Dependency Injection (`Container`):** The framework uses a custom DI container (`\Modular\Framework\Container\ConfigurableContainer`) that allows for fine-grained control over object instantiation and dependency management. The `\Modular\Framework\Container\ServiceDefinition` class is used to define how components are created and configured.
- **Import/Export Mechanism:** Modules can share components with each other through an explicit import/export mechanism.
    - **Exporting:** A module can expose its components to other modules by implementing the `\Modular\Framework\PowerModule\Contract\ExportsComponents` interface.
    - **Importing:** A module can consume components from other modules by implementing the `\Modular\Framework\PowerModule\Contract\ImportsComponents` interface. This makes dependencies between modules explicit and controlled.
- **Application (`App`):** The `\Modular\Framework\App\ModularApp` class is the entry point of the application. It is responsible for registering modules and managing the root DI container.
- **Dependency Sorting:** Module dependencies are resolved using an iterative topological sort algorithm (`\Modular\Framework\PowerModule\IterativeModuleDependencySorter`), which is then cached to improve performance on subsequent requests.

## Key Components and Directories

- `src/PowerModule/`: Contains the core interfaces and classes for creating modules (`PowerModule`, `ExportsComponents`, `ImportsComponents`, `ModuleDependencySorter`).
- `src/Container/`: Implements the Dependency Injection container (`ConfigurableContainer`, `ServiceDefinition`).
- `src/App/`: Contains the application factory (`ModularAppFactory`) and the main application class (`ModularApp`).
- `src/Config/`: Handles configuration loading.
- `src/Cache/`: Contains the PSR-16 cache implementation.
- `test/`: Contains unit tests for the framework components.

## Developer Workflows

The project uses a `Makefile` to streamline common development tasks.

- **Run tests:**
  ```sh
  make test
  ```
- **Check code style:**
  ```sh
  make codestyle
  ```
- **Run static analysis:**
  ```sh
  make phpstan
  ```

## Code Conventions

- **Interfaces:** Components are often defined by interfaces, and the DI container is used to bind implementations to these interfaces.
- **Strict Types:** The codebase uses strict typing (`declare(strict_types=1);`).
- **PSR Standards:** The code follows PSR standards for autoloading (PSR-4), container interoperability (PSR-11), and simple caching (PSR-16).

When adding new features or fixing bugs, please adhere to these architectural principles and conventions. Ensure that new modules are properly encapsulated and that dependencies between modules are explicitly defined.
