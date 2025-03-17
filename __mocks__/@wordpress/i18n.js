export const __ = ( value ) => value;
export const _n = ( singular, plural, num ) => num <= 1 ? singular : plural;
export const sprintf = ( text, replacement ) => text.replace( /%d|%s/, replacement );
