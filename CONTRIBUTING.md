# Contributing to JA-CMS

Thank you for your interest in contributing to JA-CMS! This document provides guidelines and instructions for contributing.

## Code of Conduct

- Be respectful and inclusive
- Welcome newcomers and help them learn
- Focus on constructive feedback
- Respect different viewpoints

## How to Contribute

### Reporting Bugs

1. Check if the bug has already been reported
2. Create a detailed bug report with:
   - Description of the issue
   - Steps to reproduce
   - Expected behavior
   - Actual behavior
   - Environment details (PHP version, Laravel version, etc.)
   - Screenshots (if applicable)

### Suggesting Features

1. Check if the feature has already been suggested
2. Create a feature request with:
   - Clear description
   - Use case
   - Proposed implementation (if any)
   - Benefits

### Pull Requests

1. **Fork the repository**
2. **Create a feature branch:**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make your changes:**
   - Follow code style guidelines
   - Write tests for new features
   - Update documentation
   - Ensure all tests pass

4. **Commit your changes:**
   ```bash
   git commit -m "Add: Description of your changes"
   ```
   
   **Commit Message Format:**
   - `Add:` for new features
   - `Fix:` for bug fixes
   - `Update:` for updates
   - `Refactor:` for refactoring
   - `Docs:` for documentation

5. **Push to your fork:**
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Create a Pull Request:**
   - Provide clear description
   - Reference related issues
   - Include screenshots (if UI changes)

## Code Style

### PHP

- Follow PSR-12 coding standard
- Use Laravel Pint for formatting
- Add PHPDoc comments
- Use type hints

**Format code before committing:**
```bash
./vendor/bin/pint
```

### JavaScript/Vue

- Follow Vue.js style guide
- Use ESLint and Prettier
- Use Composition API
- Use TypeScript where applicable

**Format code before committing:**
```bash
npm run lint:fix
```

## Testing

### Writing Tests

- Write tests for all new features
- Ensure test coverage doesn't decrease
- Use descriptive test names
- Follow AAA pattern (Arrange, Act, Assert)

**Run tests:**
```bash
php artisan test
```

### Test Structure

```php
public function test_feature_does_something(): void
{
    // Arrange
    $user = $this->createUser();
    
    // Act
    $response = $this->actingAs($user)->get('/api/endpoint');
    
    // Assert
    $response->assertStatus(200);
}
```

## Documentation

### Code Documentation

- Add PHPDoc comments to all classes and methods
- Document parameters and return types
- Include examples for complex functions

**Example:**
```php
/**
 * Create a new content item.
 *
 * @param  array  $data
 * @return \App\Models\Content
 * @throws \Illuminate\Validation\ValidationException
 */
public function createContent(array $data): Content
{
    // Implementation
}
```

### User Documentation

- Update user guides for new features
- Add screenshots for UI changes
- Keep documentation up to date

## Development Workflow

1. **Create issue** (if needed)
2. **Fork and clone** repository
3. **Create branch** from `main`
4. **Make changes** following guidelines
5. **Write tests** for new features
6. **Run tests** and ensure they pass
7. **Format code** with Pint/ESLint
8. **Update documentation**
9. **Commit changes** with clear messages
10. **Push and create PR**

## Review Process

1. PR will be reviewed by maintainers
2. Address any feedback
3. Ensure CI checks pass
4. PR will be merged when approved

## Questions?

- Open an issue for questions
- Check existing documentation
- Read the [Detailed Agent & Developer Guidelines](docs/guidelines/AGENT_GUIDE.md)
- Ask in discussions

Thank you for contributing! 🎉

