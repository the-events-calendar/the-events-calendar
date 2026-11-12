# The Events Calendar Documentation

This directory contains documentation for selected features of The Events Calendar plugin.

## Documented Features

### 1. REST API V1

Complete documentation for the new TEC REST API.

- **Location**: [REST/TEC/V1/](REST/TEC/V1/)
- **Overview**: Modern REST API implementation for events, venues, and organizers
- **Key Features**:
  - Full CRUD operations
  - OpenAPI 3.0 documentation
  - Type-safe parameters
  - ORM integration

### 2. RBAC (roles & capabilities)

Who can do what in The Events Calendar, and where that is decided in code.

- **Location**: [RBAC/](RBAC/)
- **Overview**: Role-to-capability model, bootstrap timing, REST and Event Aggregator access rules, with Mermaid diagrams
- **HTML view**: [RBAC/index.html](RBAC/index.html) renders the same document standalone (open it from disk)

## Future Documentation

Additional features will be documented here as needed, including:

- Event management workflows
- Templating system
- Hook references
- Integration guides

## Contributing

When adding documentation for new features:

1. Create a new directory for the feature
2. Include a README.md with an overview
3. Update this file to list the new documentation
4. Follow the existing documentation patterns