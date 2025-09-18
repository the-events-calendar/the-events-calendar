// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import * as React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import { beforeEach, afterEach, describe, expect, it, jest } from '@jest/globals';
import { Fill, Slot, SlotFillProvider } from '@wordpress/components';
import { POST_TYPE_EVENT } from '@tec/events/classy/constants';
import mockWpDataModule from '../_support/mockWpDataModule';
import TestProvider from '../_support/TestProvider';

// Mock the @wordpress/data module before importing renderFields.
const { mockSelect, mockUseDispatch } = mockWpDataModule();

// Import renderFields after setting up mocks
import { default as renderFields } from '@tec/events/classy/functions/renderFields';

// Mock the field components to avoid complex WordPress component dependencies.
jest.mock( '@tec/common/classy/fields', () => ( {
	PostTitle: ( { title }: { title: string } ) => <div data-testid="post-title">{ title }</div>,
} ) );

// Mock the field components to allow testing their rendering without pulling in complex dependencies.
// The components are tested in their own test files.
jest.mock( '@tec/events/classy/fields', () => ( {
	EventAdmission: ( { title }: { title: string } ) => <div data-testid="event-admission">{ title }</div>,
	EventDateTime: ( { title }: { title: string } ) => <div data-testid="event-datetime">{ title }</div>,
	EventDetails: ( { title }: { title: string } ) => <div data-testid="event-details">{ title }</div>,
	EventLocation: ( { title }: { title: string } ) => <div data-testid="event-location">{ title }</div>,
	EventOrganizer: ( { title }: { title: string } ) => <div data-testid="event-organizer">{ title }</div>,
} ) );

describe( 'renderFields', () => {
	let mockEditPost;

	// Mock currency options.
	const currencyOptions = [
		{ value: 'USD', label: 'US Dollar' },
		{ value: 'EUR', label: 'Euro' },
		{ value: 'GBP', label: 'British Pound' },
	];

	const setupMocks = ( meta = {}, isEvent: boolean = true ) => {
		mockEditPost = jest.fn();

		mockSelect.mockImplementation( ( store: string ): any => {
			if ( store === 'core/editor' ) {
				return {
					getEditedPostAttribute: ( attribute: string ): any => {
						switch ( attribute ) {
							case 'meta':
								return meta;
							case 'type':
								return isEvent ? POST_TYPE_EVENT : 'post';
							case 'title':
								return 'Test Event Title';
							case 'featured_media':
								return 0;
							default:
								return null;
						}
					},
					getEditedPostContent: () => 'Test event content',
					getCurrentPost: () => ( {
						id: 1,
						type: isEvent ? POST_TYPE_EVENT : 'post',
						status: 'publish',
						title: 'Test Event Title',
						content: 'Test event content',
						featured_media: 0,
					} ),
					getCurrentPostType: () => ( isEvent ? POST_TYPE_EVENT : 'post' ),
					getCurrentPostId: () => 1,
				};
			}

			if ( store === 'core/media' ) {
				return {
					getMedia: () => null,
					getMediaItem: () => null,
					getMediaItemByType: () => null,
					getMediaItemsByType: () => [],
				};
			}

			if ( store === 'tec/classy' ) {
				return {
					getCurrencyOptions: () => currencyOptions,
					getDefaultCurrency: () => currencyOptions[ 0 ],
					getVenuesLimit: () => 10,
					getOrganizersLimit: () => 10,
					areTicketsSupported: () => true,
					isUsingTickets: () => false,
				};
			}

			if ( store === 'tec/classy/events' ) {
				return {
					getCurrencyOptions: () => currencyOptions,
					getDefaultCurrency: () => currencyOptions[ 0 ],
					getVenuesLimit: () => 10,
					getOrganizersLimit: () => 10,
					areTicketsSupported: () => true,
					isUsingTickets: () => false,
					isNewEvent: () => false,
					getEventDateTimeDetails: () => ( {
						startDate: '2024-01-01T10:00:00.000Z',
						endDate: '2024-01-01T11:00:00.000Z',
						startTime: '10:00',
						endTime: '11:00',
						allDay: false,
						timezone: 'UTC',
					} ),
					getEventCost: () => ( {
						cost: '',
						currency: 'USD',
						currencyPosition: 'prefix',
						currencySymbol: '$',
					} ),
					getEventOrganizer: () => null,
					getEventVenue: () => null,
				};
			}

			// Throw an error for unknown stores to help identify what needs mocking.
			throw new Error( `Unknown store requested in mockSelect: ${ store }` );
		} );

		mockUseDispatch.mockImplementation( ( store: string ): any => {
			if ( store === 'core/editor' ) {
				return {
					editPost: mockEditPost,
				};
			}

			if ( store === 'core/media' ) {
				return {
					uploadMedia: jest.fn(),
					createMediaFromFile: jest.fn(),
					editMedia: jest.fn(),
					deleteMedia: jest.fn(),
				};
			}

			if ( store === 'tec/classy/events' ) {
				return {
					updateEventMeta: jest.fn(),
					setEventDateTimeDetails: jest.fn(),
					setEventCost: jest.fn(),
					setEventOrganizer: jest.fn(),
					setEventVenue: jest.fn(),
				};
			}

			// Throw an error for unknown stores to help identify what needs mocking.
			throw new Error( `Unknown store requested in mockUseDispatch: ${ store }` );
		} );
	};

	beforeEach( () => {
		jest.resetModules();
		jest.clearAllMocks();
	} );

	afterEach( () => {
		jest.resetModules();
	} );

	it( 'renders nothing if post is not Event', () => {
		setupMocks( {}, false );

		const TestComponent = () => renderFields( null );

		const { container } = render(
			<TestProvider>
				<SlotFillProvider>
					<TestComponent />
					<Slot name="tec.classy.fields" />
				</SlotFillProvider>
			</TestProvider>
		);

		expect( container ).toMatchSnapshot();

		// Check that the main elements are not rendered.
		expect( () => screen.getByTestId( 'post-title' ) ).toThrowError();
		expect( () => screen.getByTestId( 'event-datetime' ) ).toThrowError();
		expect( () => screen.getByTestId( 'event-details' ) ).toThrowError();
		expect( () => screen.getByTestId( 'event-location' ) ).toThrowError();
		expect( () => screen.getByTestId( 'event-organizer' ) ).toThrowError();
		expect( () => screen.getByTestId( 'event-admission' ) ).toThrowError();
	} );

	it( 'renders all fields if post is Event', () => {
		setupMocks();

		const TestComponent = () => renderFields( null );

		const { container } = render(
			<TestProvider>
				<SlotFillProvider>
					<TestComponent />
					<Slot name="tec.classy.fields" />
				</SlotFillProvider>
			</TestProvider>
		);

		expect( container ).toMatchSnapshot();

		// Check that the main elements are rendered. More detailed tests for each field are in their own test files.
		expect( screen.getByTestId( 'post-title' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'event-datetime' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'event-details' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'event-location' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'event-organizer' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'event-admission' ) ).toBeInTheDocument();
	} );

	it( 'renders additional fields added via the tec.classy.fields Slot', () => {
		setupMocks();

		const TestComponent = () => renderFields( null );

		const { container } = render(
			<TestProvider>
				<SlotFillProvider>
					<TestComponent />
					<Fill name="tec.classy.fields">
						<p>Additional Field 1</p>
						<p>Additional Field 2</p>
					</Fill>
					<Slot name="tec.classy.fields" />
				</SlotFillProvider>
			</TestProvider>
		);

		expect( container ).toMatchSnapshot();

		// Check that the additional fields are rendered.
		expect( screen.getByText( 'Additional Field 1' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Additional Field 2' ) ).toBeInTheDocument();
	} );
} );
