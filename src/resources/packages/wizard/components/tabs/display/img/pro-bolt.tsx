import * as React from 'react';
const SVGComponent = ( props ) => (
	<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 12 12" { ...props }>
		<g clipPath="url(#a)">
			<path fill="#FFCF48" d="M11.879 6.072a5.927 5.927 0 1 1-11.855 0 5.927 5.927 0 0 1 11.855 0Z" />
			<path fill="#0F1031" d="m5.675 6.118-2.073-.06 4.702-4.324L6.246 5.6l2.073.06-4.412 4.683 1.768-4.224Z" />
		</g>
		<defs>
			<clipPath id="a">
				<path fill="#fff" d="M0 0h12v12H0z" />
			</clipPath>
		</defs>
	</svg>
);
export default SVGComponent;
