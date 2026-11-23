import type { TextPart } from '@/types';

/**
 * Small text helpers for admin-editable copy.
 */

/**
 * Replace {{token}} placeholders, e.g. "Hi, I'm {{name}}." or a footer link
 * "mailto:{{email}}". Unknown tokens become empty strings.
 */
export function fillTokens(
    text: string | null | undefined,
    tokens: Record<string, string | number | null | undefined>,
): string {
    return (text ?? '').replace(/\{\{\s*(\w+)\s*\}\}/g, (_, key: string) =>
        String(tokens[key] ?? ''),
    );
}

/**
 * Split a headline around its highlighted phrase, so the phrase can be set in
 * the accent (italic serif) without v-html. Case-insensitive; when the phrase
 * is missing the whole title is one plain part.
 */
export function highlightParts(
    title: string | null | undefined,
    highlight: string | null | undefined,
): TextPart[] {
    const text = title ?? '';
    const phrase = (highlight ?? '').trim();

    if (!phrase) {
        return [{ text, highlight: false }];
    }

    const index = text.toLowerCase().indexOf(phrase.toLowerCase());

    if (index === -1) {
        return [{ text, highlight: false }];
    }

    return [
        { text: text.slice(0, index), highlight: false },
        { text: text.slice(index, index + phrase.length), highlight: true },
        { text: text.slice(index + phrase.length), highlight: false },
    ].filter((part) => part.text !== '');
}

/** "2019 — Present" style range from two ISO dates. */
export function yearRange(
    start: string | null | undefined,
    end: string | null | undefined,
    presentLabel = 'Present',
): string {
    const startYear = start ? new Date(start).getFullYear() : null;
    const endLabel = end ? new Date(end).getFullYear() : presentLabel;

    return startYear ? `${startYear} — ${endLabel}` : String(endLabel);
}

/** "Jan 2022" from an ISO date. */
export function monthYear(date: string | null | undefined): string {
    if (!date) {
        return '';
    }

    return new Date(date).toLocaleDateString('en', {
        month: 'short',
        year: 'numeric',
    });
}

/** "12 Jan 2026" from an ISO date, in the visitor's locale. */
export function formatDate(date: string): string {
    return new Date(date).toLocaleDateString(undefined, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

/**
 * "2019 — Present" (style "year") or "Jan 2019 — Mar 2022" (style "month")
 * from two ISO dates, in the visitor's locale. A missing date reads "Present".
 */
export function formatPeriod(
    start: string | null,
    end: string | null,
    style: 'year' | 'month',
): string {
    const formatOne = (date: string | null): string => {
        if (!date) {
            return 'Present';
        }

        return style === 'year'
            ? new Date(date).getFullYear().toString()
            : new Date(date).toLocaleDateString(undefined, {
                  month: 'short',
                  year: 'numeric',
              });
    };

    return `${formatOne(start)} — ${formatOne(end)}`;
}

/** Text paragraphs from a multi-line string, blank lines removed. */
export function paragraphs(text: string | null | undefined): string[] {
    return (text ?? '')
        .split(/\n{2,}/)
        .map((part) => part.trim())
        .filter(Boolean);
}
