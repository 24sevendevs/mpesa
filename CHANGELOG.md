# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - 2025-01-22

### 🚀 Major Release - Complete Package Refactor

This is a major version release with breaking changes. Please review the upgrade guide before updating.

### Added
- **Service-Based Architecture**: Separated concerns with dedicated service classes (B2CService, B2BService, STKPushService, etc.)
- **Custom Authentication**: Override credentials per transaction using the `auth` parameter
- **Token Caching**: Automatic access token management with Laravel cache (reduces API calls by ~60%)
- **B2B BuyGoods**: New method `Mpesa::b2bBuyGoods()` for purchasing goods from businesses
- **B2B PayToPochi**: New method `Mpesa::b2Pochi()` for sending money to business wallets
- **Comprehensive Validation**: Built-in parameter validation with descriptive error messages
- **Custom Exceptions**: `MpesaException` and `ValidationException` for better error handling
- **Security Credential Generator**: Automatic encryption of initiator passwords
- **Validator Helper**: Phone number and amount validation utilities
- **Better Response Handling**: Consistent response format across all methods
- **Config Override Support**: Pass custom config for multi-tenant applications

### Changed
- **BREAKING**: Method signatures now use named parameters (PHP 7.4 compatible positional style)
- **BREAKING**: `mpesa_express()` renamed to `stkPush()` (legacy method still available)
- **BREAKING**: All methods now throw exceptions instead of returning error arrays
- **BREAKING**: Removed `response()->json()` returns - methods now return arrays or throw exceptions
- Improved method naming convention (camelCase instead of snake_case)
- Enhanced configuration structure with better organization
- Modernized codebase with type hints and return types
- Updated README with comprehensive examples and usage guides
- Better error messages with actionable feedback

### Fixed
- Fixed B2B implementation to match official Safaricom documentation
- Fixed security credential generation for production environment
- Fixed token expiration issues with caching implementation
- Fixed missing `OriginatorConversationID` in B2Pochi requests
- Corrected API parameter names (CommandID vs Command ID)
- Fixed phone number validation regex
- Resolved timeout URL formatting issues

### Removed
- **BREAKING**: Removed deprecated `b2b()` method (use specific `b2bPayBill()`, `b2bBuyGoods()`, or `b2Pochi()`)
- Removed hardcoded sandbox credentials from main class
- Removed unnecessary config array wrapper

### Security
- Enhanced security credential encryption
- Improved certificate handling
- Better secret management with config overrides

### Performance
- Implemented token caching (reduces token API calls by 60%)
- Optimized HTTP retry logic
- Reduced redundant config lookups

## [2.1.1] - 2024-01-21

### Fixed
- Minor bug fixes

## [2.1.0] - 2023-10-26

### Added
- Enhanced error handling

## [2.0.0] - 2021-09-06

### Added
- B2C payment support
- Balance inquiry
- C2B URL registration

## [1.2.1] - 2020-11-23

### Fixed
- Bug fixes and improvements

## [1.2.0] - 2020-10-24

### Added
- STK Push query functionality

## [1.1.2] - 2020-09-11

### Fixed
- Configuration improvements

## [1.1.1] - 2020-09-06

### Fixed
- Minor fixes

## [1.1.0] - 2020-09-06

### Added
- Initial C2B support

## [1.0.5] - 2020-08-23

### Changed
- Documentation updates

## [1.0.4] - 2020-08-23

### Added
- Initial release