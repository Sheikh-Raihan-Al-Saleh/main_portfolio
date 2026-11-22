/**
 * A piece of syntax-coloured code for CodeEditor. `kind` picks the colour;
 * plain text has no kind.
 */
export type CodeToken = {
    text: string;
    kind?: 'kw' | 'str' | 'fn' | 'num' | 'com' | 'punc' | 'prop' | 'type';
};
