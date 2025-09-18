# Jest Integration Tests for Classy Features

This guide provides instructions for writing and maintaining Jest integration tests for The Events Calendar's Classy
features. These tests verify that components, API calls, and store interactions work correctly together in a realistic
environment.

## Test Philosophy

The Classy test suite prioritizes integration testing over unit testing. Tests should verify that features work as users
experience them, with minimal mocking. Mock only what's necessary to isolate the code under test from external
dependencies:

- **WordPress Data Module** (`@wordpress/data`) - Mock selectors and dispatch functions to control state
- **WordPress API Fetch** (`@wordpress/api-fetch`) - Mock network requests to test API interactions
- **External services** - Mock only when absolutely necessary

## Running Tests

### Run All Tests

```bash
nvm use && npm run classy:jest
```

### Run a Single Test File

```bash
nvm use && node node_modules/.bin/jest --config tests/classy_jest/jest.config.ts <test_file>

# Example
nvm use && node node_modules/.bin/jest --config tests/classy_jest/jest.config.ts tests/classy_jest/fields/EventOrganizer.spec.tsx
```

### Run a Single Test Method

```bash
nvm use && node node_modules/.bin/jest --config tests/classy_jest/jest.config.ts <test_file> -t "<test_method>"

# Example
nvm use && node node_modules/.bin/jest --config tests/classy_jest/jest.config.ts tests/classy_jest/fields/EventOrganizer.spec.tsx -t "should render the event organizer component with default state"
```

## Test Structure

```
tests/classy_jest/
├── _support/           # Test utilities and helpers
│   ├── TestProvider.tsx       # React context provider for tests
│   └── mockWpDataModule.ts    # WordPress data mocking utility
├── api/                # API integration tests
├── fields/             # Component field tests
├── functions/          # Function tests
├── store/              # Store selector tests
├── jest.config.ts      # Jest configuration
├── jest.setup.ts       # Global test setup
└── test-guide.md       # This guide
```

### Test File Naming

- Component tests: `ComponentName.spec.tsx` (for React components)
- API tests: `endpoint-name.spec.ts` (for API endpoints)
- Function tests: `functionName.spec.ts` or `functionName.spec.tsx` (for utility functions)
- Store tests: `selectors.spec.ts`, `actions.spec.ts` (for Redux/store tests)

## Writing Tests

### Test File Headers

Most test files in the Classy suite begin with these TypeScript compiler directives:

```tsx
// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
```

These headers serve important purposes:
- `@ts-nocheck`: Disables TypeScript type checking for the test file. This is useful when mocking complex types or when test utilities don't have complete type definitions.
- `/// <reference types="jest" />`: Ensures Jest global types are available in the file, providing proper type hints for Jest functions like `describe`, `it`, `expect`, etc.

### Component Tests

Component tests verify that UI components render correctly and handle user interactions. Use `TestProvider` to wrap
components with necessary context and `mockWpDataModule` to control WordPress data.

#### Basic Component Test Structure

```tsx
// tests/classy_jest/fields/EventOrganizer.spec.tsx
// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import * as React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';
import { beforeEach, describe, expect, it, jest, beforeAll, afterAll } from '@jest/globals';
import mockWpDataModule from '../_support/mockWpDataModule';
import TestProvider from '../_support/TestProvider';

// Get mock functions before importing components that use them
const { mockSelect, mockUseDispatch } = mockWpDataModule();

// Mock API functions if needed
jest.mock('@tec/events/classy/api/organizers', () => ({
	fetchOrganizers: jest.fn(),
	upsertOrganizer: jest.fn(),
}));

// Import components after mocking to ensure mocks are used
import EventOrganizer from '@tec/events/classy/fields/EventOrganizer/EventOrganizer';
import { fetchOrganizers, upsertOrganizer } from '@tec/events/classy/api/organizers';

describe('EventOrganizer', () => {
	let mockEditPost;
	let mockFetchOrganizers;
	let mockUpsertOrganizer;

	const setupMocks = (meta = {}) => {
		mockEditPost = jest.fn();
		mockFetchOrganizers = jest.fn();
		mockUpsertOrganizer = jest.fn();

		// Connect mock implementations
		(fetchOrganizers as jest.Mock).mockImplementation(mockFetchOrganizers);
		(upsertOrganizer as jest.Mock).mockImplementation(mockUpsertOrganizer);

		// Mock WordPress data selectors
		mockSelect.mockImplementation((store: string): any => {
			if (store === 'core/editor') {
				return {
					getEditedPostAttribute: (attribute: string): any => {
						return attribute === 'meta' ? meta : null;
					},
				};
			}
			throw new Error(`Unknown store requested: ${store}`);
		});

		// Mock WordPress dispatch functions
		mockUseDispatch.mockImplementation((store: string): any => {
			if (store === 'core/editor') {
				return {
					editPost: mockEditPost,
				};
			}
			throw new Error(`Unknown store requested: ${store}`);
		});
	};

	beforeAll(() => {
		jest.resetModules();
		jest.clearAllMocks();
	});

	afterAll(() => {
		jest.resetModules();
	});

	beforeEach(() => {
		jest.clearAllMocks();
	});

	it('should render with default state', () => {
		setupMocks();
		mockFetchOrganizers.mockResolvedValue({ organizers: [], total: 0 });

		render(
			<TestProvider>
				<EventOrganizer/>
			</TestProvider>
		);

		expect(screen.getByRole('button')).toBeInTheDocument();
	});

	it('should handle user interaction', async () => {
		setupMocks({_EventOrganizerID: []});

		render(
			<TestProvider>
				<EventOrganizer/>
			</TestProvider>
		);

		const button = screen.getByRole('button');
		fireEvent.click(button);

		await waitFor(() => {
			expect(mockEditPost).toHaveBeenCalledWith({
				meta: expect.objectContaining({
					_EventOrganizerID: expect.any(Array),
				}),
			});
		});
	});
});
```

### API Tests

API tests verify that functions correctly interact with WordPress REST API endpoints. Mock `@wordpress/api-fetch` to
control responses without making actual network requests.

#### API Test Example

```ts
// tests/classy_jest/api/venues.spec.ts
import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, jest, test } from '@jest/globals';
import apiFetch from '@wordpress/api-fetch';
import { fetchVenues, upsertVenue } from '@tec/events/classy/api';
import { FetchedVenue } from '@tec/events/classy/types/FetchedVenue';
import { VenueData } from '@tec/events/classy/types/VenueData';
import { PostStatus } from '@tec/common/classy/types/Api';

jest.mock('@wordpress/api-fetch', () => ({
	__esModule: true,
	default: jest.fn(),
}));

describe('Venue API', () => {
	beforeEach(() => {
		jest.clearAllMocks();
	});

	test('fetchVenues should retrieve and map venue data', async () => {
		const mockResponse = {
			venues: [{
				id: 1,
				title: {rendered: 'Sample Venue'},
				address: '123 Main St',
				city: 'New York',
				state_province: 'NY',
				zip: '10001',
			}],
			total: 1,
		};

		(apiFetch as jest.Mock).mockResolvedValue(mockResponse);

		const result = await fetchVenues({search: 'Sample'});

		expect(apiFetch).toHaveBeenCalledWith({
			path: expect.stringContaining('/tec/v1/venues'),
			method: 'GET',
		});

		expect(result.venues[0]).toEqual({
			id: 1,
			venue: 'Sample Venue',
			address: '123 Main St',
			city: 'New York',
			state: 'NY',
			zip: '10001',
		});
	});

	test('upsertVenue should create new venue', async () => {
		const venueData = {
			venue: 'New Venue',
			address: '456 Oak Ave',
			city: 'Los Angeles',
			state: 'CA',
		};

		const mockResponse = {id: 2, ...venueData};
		(apiFetch as jest.Mock).mockResolvedValue(mockResponse);

		const result = await upsertVenue(venueData);

		expect(apiFetch).toHaveBeenCalledWith({
			path: '/wp/v2/tribe_venue',
			method: 'POST',
			data: expect.objectContaining(venueData),
		});

		expect(result.id).toBe(2);
	});
});
```

### Store Selector Tests

Store selectors transform raw data into useful formats. Test selectors by mocking their dependencies and verifying
output.

```ts
// tests/classy_jest/store/selectors.spec.ts
// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import { describe, expect, it, jest, beforeEach, afterEach } from '@jest/globals';
import { getPostMeta, getEventDateTimeDetails } from '@tec/events/classy/store/selectors';
import { select } from '@tec/common/classy/store';
import { StoreState } from '@tec/events/classy/types/Store';
import { EventMeta } from '@tec/events/classy/types/EventMeta';

jest.mock('@tec/common/classy/store', () => ({
	select: jest.fn(),
}));

const mockSelect = select as jest.MockedFunction<typeof select>;

describe('Store Selectors', () => {
	beforeEach(() => {
		jest.clearAllMocks();
	});

	it('getPostMeta should return event metadata', () => {
		const mockMeta = {
			_EventStartDate: '2024-01-15 10:00:00',
			_EventEndDate: '2024-01-15 18:00:00',
			_EventAllDay: false,
		};

		mockSelect.mockImplementation((store: string) => {
			if (store === 'core/editor') {
				return {
					getEditedPostAttribute: jest.fn().mockReturnValue(mockMeta),
				};
			}
			return {};
		});

		const state = {};
		const result = getPostMeta(state);

		expect(result).toEqual(mockMeta);
	});
});
```

## Mocking External Components

When testing components that depend on complex external components, mock them to simplify testing:

```tsx
// Mock TinyMCE editor component
jest.mock('@tec/common/classy/components', () => ({
	TinyMceEditor: jest.fn(({ content, onChange, id }) => (
		<textarea
			data-testid={id}
			value={content}
			onChange={(e) => onChange(e.target.value)}
			aria-label="Description editor"
		/>
	)),
}));

// Mock WordPress editor components
jest.mock('@wordpress/editor', () => ({
	PostFeaturedImage: jest.fn(() => <div data-testid="featured-image-component">Featured Image Component</div>),
}));

// Mock utility functions
jest.mock('@tec/common/classy/functions', () => ({
	isValidUrl: jest.fn((url: string) => {
		if (!url) return true; // Empty is valid
		try {
			const urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
			return urlPattern.test(url) || url.startsWith('www.');
		} catch {
			return false;
		}
	}),
}));
```

## Best Practices

### 1. Minimal Mocking

Only mock what's necessary to isolate the code under test. Prefer integration tests that exercise real implementations
when possible.

```tsx
// Good: Mock only WordPress data layer
jest.mock('@wordpress/data', () => ({
	...jest.requireActual('@wordpress/data'),
	useSelect: jest.fn(),
	useDispatch: jest.fn(),
}));

// Avoid: Over-mocking internal modules
// jest.mock('@tec/events/classy/utils'); // Don't mock internal utilities
```

### 2. Use Test Helpers

The `_support` directory contains utilities to reduce boilerplate:

- **TestProvider**: Wraps components with WordPress data registry context
- **mockWpDataModule**: Provides consistent WordPress data mocking

```tsx
import TestProvider from '../_support/TestProvider';
import mockWpDataModule from '../_support/mockWpDataModule';

const {mockSelect, mockUseDispatch} = mockWpDataModule();

// Use in tests...
render(
	<TestProvider>
		<YourComponent/>
	</TestProvider>
);
```

### 3. Test User Interactions

Focus on testing how users interact with components rather than implementation details.

```tsx
// Good: Test user-visible behavior
it('should save organizer when user clicks save', async () => {
	render(<EventOrganizer/>);

	const input = screen.getByRole('textbox');
	const saveButton = screen.getByText('Save');

	await userEvent.type(input, 'New Organizer');
	await userEvent.click(saveButton);

	expect(mockEditPost).toHaveBeenCalledWith({
		meta: expect.objectContaining({
			_EventOrganizerID: expect.any(Array),
		}),
	});
});

// Avoid: Testing implementation details
it('should call internal handler', () => {
	// Don't test private methods or internal state
});
```

### 4. Clear Test Data

Define test data clearly and reuse it across related tests.

```ts
// Define at the top of the test file
const mockOrganizers = [
	{
		id: 1,
		organizer: 'Sample Organizer 1',
		phone: '+1-555-0123',
		email: 'organizer1@example.com',
		website: 'https://organizer1.com',
	},
	// ...
];

// Use in multiple tests
it('should display organizers', () => {
	mockFetchOrganizers.mockResolvedValue({
		organizers: mockOrganizers,
		total: mockOrganizers.length
	});
	// ...
});
```

### 5. Wait for Async Operations

Use `waitFor` and `act` for asynchronous operations to avoid test flakiness.

```tsx
import {waitFor, act} from '@testing-library/react';

it('should load data asynchronously', async () => {
	render(<Component/>);

	await waitFor(() => {
		expect(screen.getByText('Loaded Data')).toBeInTheDocument();
	});
});

it('should update state', async () => {
	const {result} = renderHook(() => useCustomHook());

	await act(async () => {
		result.current.updateData('new value');
	});

	expect(result.current.data).toBe('new value');
});
```

## Common Patterns

### Testing Components with Modals

```tsx
it('should open modal when button clicked', async () => {
	render(
		<TestProvider>
			<EventLocation/>
		</TestProvider>
	);

	const addButton = screen.getByRole('button', {name: /add venue/i});
	fireEvent.click(addButton);

	await waitFor(() => {
		expect(screen.getByRole('dialog')).toBeInTheDocument();
		expect(screen.getByText('Create New Venue')).toBeInTheDocument();
	});
});
```

### Testing Form Validation

```tsx
it('should validate required fields', async () => {
	render(
		<TestProvider>
			<EventDetails/>
		</TestProvider>
	);

	const submitButton = screen.getByRole('button', {name: /save/i});
	fireEvent.click(submitButton);

	await waitFor(() => {
		expect(screen.getByText('Title is required')).toBeInTheDocument();
	});
});
```

### Testing Error Handling

```tsx
it('should display error when API fails', async () => {
	const error = new Error('Network error');
	mockFetchVenues.mockRejectedValue(error);

	render(
		<TestProvider>
			<VenueSelector/>
		</TestProvider>
	);

	await waitFor(() => {
		expect(screen.getByText(/error loading venues/i)).toBeInTheDocument();
	});
});
```

## Troubleshooting

### Common Issues

1. **"Unknown store requested" error**
   - Ensure you're mocking all stores your component uses
   - Add the store to your mock implementation
   - Example fix:
   ```tsx
   mockSelect.mockImplementation((store: string): any => {
     if (store === 'core/editor') {
       // ... editor store mocks
     }
     if (store === 'tec/classy') {  // Add missing store
       return {
         getSettings: jest.fn().mockReturnValue(mockSettings),
       };
     }
     throw new Error(`Unknown store requested: ${store}`);
   });
   ```

2. **Act warnings**
   - Wrap state updates in `act()` or use `waitFor()`
   - These are often suppressed for external libraries in jest.setup.ts
   - Example fix:
   ```tsx
   await act(async () => {
     fireEvent.click(button);
   });
   ```

3. **Cannot find element**
   - Use appropriate queries (getByRole, getByText, etc.)
   - Wait for async rendering with `waitFor()`
   - Check that TestProvider wraps your component
   - Use `screen.debug()` to see current DOM state

4. **Mock not being called**
   - Clear mocks in `beforeEach()`
   - Verify mock setup occurs before rendering
   - Check that the component actually calls the mocked function
   - Ensure mocks are set up before importing components that use them

5. **TypeScript errors in test files**
   - Add `// @ts-nocheck` at the top of the file if needed
   - Ensure `/// <reference types="jest" />` is present for Jest types
   - Cast mocked functions: `(fetchData as jest.Mock).mockResolvedValue(...)`

### Debug Tips

```tsx
// Print the current DOM
screen.debug();

// Print specific element
screen.debug(screen.getByRole('button'));

// Use testing playground
screen.logTestingPlaygroundURL();

// Check what queries are available
const { container } = render(<Component />);
console.log(container.innerHTML);

// Log mock calls
console.log('mockEditPost calls:', mockEditPost.mock.calls);
console.log('mockEditPost last call:', mockEditPost.mock.lastCall);
```

## Additional Resources

- [Jest Documentation](https://jestjs.io/docs/getting-started)
- [React Testing Library](https://testing-library.com/docs/react-testing-library/intro/)
- [Testing Library Queries](https://testing-library.com/docs/queries/about)
- [WordPress Data Module](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-data/)
