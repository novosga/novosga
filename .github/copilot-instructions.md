# NovoSGA Copilot Instructions

## Project Overview

NovoSGA is a customer queue management system written in PHP using the Symfony framework. It's designed to manage customer service queues, widely used by public institutions and companies across multiple countries. The system supports internationalization with translations in English, Spanish, and Portuguese.

## Technology Stack

- **Language**: PHP 8.2+
- **Framework**: Symfony 7.1
- **Database**: MySQL 5.7+ or PostgreSQL 15+
- **ORM**: Doctrine ORM 3.2+
- **Frontend**: Symfony UX (Stimulus, Turbo, Icons)
- **Runtime**: FrankenPHP (Symfony Runtime)
- **Authentication**: OAuth2 Server (league/oauth2-server-bundle)

## Architecture

- **Bundles**: The application is modular, using several NovoSGA bundles:
  - `novosga/core`: Core functionality
  - `novosga/attendance-bundle`: Customer service management
  - `novosga/customers-bundle`: Customer management
  - `novosga/monitor-bundle`: Queue monitoring
  - `novosga/reports-bundle`: Reporting functionality
  - `novosga/scheduling-bundle`: Scheduling features
  - `novosga/settings-bundle`: Application settings
  - `novosga/triage-bundle`: Queue triage
  - `novosga/users-bundle`: User management

- **Directory Structure**:
  - `src/`: Application code (Commands, Controllers, Entities, Services, etc.)
  - `config/`: Configuration files
  - `templates/`: Twig templates
  - `public/`: Web-accessible assets
  - `tests/`: PHPUnit tests
  - `migrations/`: Doctrine migrations
  - `translations/`: i18n files

## Coding Standards

### PHP Standards
- Follow **PSR-12** coding standard
- Use **type hints** for parameters and return types
- Run `vendor/bin/phpcs` to check code standards
- Run `vendor/bin/phpstan` for static analysis
- Code should pass both checks before committing

### Naming Conventions
- Classes: PascalCase
- Methods/Functions: camelCase
- Constants: UPPER_SNAKE_CASE
- Database tables/columns: snake_case

### Documentation
- Use PHPDoc comments for classes, methods, and properties
- Include `@param`, `@return`, and `@throws` annotations
- Document complex logic with inline comments

## Development Workflow

### Installation & Setup
```bash
# Install dependencies
composer install

# Generate JWT certificates for OAuth2
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem 2048
openssl rsa -in config/jwt/private.pem -pubout -out config/jwt/public.pem

# Run migrations
bin/console doctrine:migrations:migrate --no-interaction

# Load fixtures (development/test only)
bin/console doctrine:fixture:load --no-interaction
```

### Testing
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test
vendor/bin/phpunit tests/Path/To/TestFile.php

# Run code standards check
vendor/bin/phpcs

# Run static analysis
vendor/bin/phpstan

# Lint YAML files
bin/console lint:yaml config
bin/console lint:yaml translations
```

### Database
- The application supports both MySQL and PostgreSQL
- Always create migrations for schema changes: `bin/console make:migration`
- Test migrations with both database engines when possible
- Never commit direct schema changes without migrations

### Environment Configuration
- Use `.env` for local configuration (not committed)
- Use `.env.test` for test environment
- Required environment variables:
  - `DATABASE_URL`: Database connection string
  - `APP_ENV`: Environment (dev, test, prod)
  - `APP_SECRET`: Application secret key

## Symfony Best Practices

### Controllers
- Keep controllers thin, move logic to services
- Use dependency injection for services
- Return Response objects or use AbstractController helpers
- Use route annotations/attributes

### Services
- Register services in `config/services.yaml`
- Use autowiring and autoconfigure when possible
- Follow SOLID principles
- Create interfaces for services that may have multiple implementations

### Entities
- Use Doctrine annotations/attributes
- Always define proper relationships (OneToMany, ManyToOne, etc.)
- Implement validation constraints
- Keep entities focused on data representation

### Forms
- Use Symfony Form component
- Create dedicated Form Types
- Apply validation in form types or entities
- Use CSRF protection

## Security

- Always validate and sanitize user input
- Use Symfony's Security component for authentication/authorization
- Never commit secrets or credentials to the repository
- Use environment variables for sensitive configuration
- Follow OWASP best practices

## Internationalization

- Store translatable strings in `translations/` directory
- Use translation keys, not hardcoded strings
- Support for: English (en), Spanish (es), Portuguese (pt_BR)
- Use Symfony's translation component: `{% trans %}` in Twig, `$translator->trans()` in PHP

## Common Commands

```bash
# Clear cache
bin/console cache:clear

# Create a new controller
bin/console make:controller

# Create a new entity
bin/console make:entity

# Create a migration
bin/console make:migration

# Update database schema (dev only)
bin/console doctrine:schema:update --force

# Run migrations
bin/console doctrine:migrations:migrate

# Load fixtures
bin/console doctrine:fixtures:load
```

## Docker Support

The project includes Docker configuration:
- `compose.yaml`: Main Docker Compose configuration
- `compose.override.yaml`: Local overrides
- `Dockerfile`: Application container definition

## CI/CD

The project uses GitHub Actions for CI:
- Runs on all branches
- Tests with both MySQL and PostgreSQL
- Checks code standards (phpcs)
- Runs static analysis (phpstan)
- Lints YAML files
- Runs PHPUnit tests

## Additional Guidelines

1. **Minimal Changes**: Make the smallest possible changes to achieve the goal
2. **Test Coverage**: Add or update tests for new functionality
3. **Documentation**: Update relevant documentation when changing behavior
4. **Backward Compatibility**: Maintain compatibility when possible
5. **Performance**: Consider performance implications of changes
6. **Code Review**: All changes should be reviewed before merging

## Resources

- Official Documentation: https://novosga.org/docs/
- Community Forum: https://discuss.novosga.org/
- Telegram Group: https://t.me/novosga
- GitHub Issues: https://github.com/novosga/novosga/issues
