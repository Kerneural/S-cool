// Exercise the source Alpine behavior using identity translations. This is not a
// Blade renderer: only literal __() and @js() strings are lowered; dynamic Blade
// expressions remain intact for the existing component-specific test adapters.
export function landingBladeLiterals(source) {
    const literal = String.raw`'(?:\\.|[^'\\])*'`;
    const identityTranslations = source.replace(
        new RegExp(String.raw`\b__\(\s*(${literal})\s*\)`, 'g'),
        (_, value) => value,
    );

    return identityTranslations.replace(
        new RegExp(String.raw`@js\(\s*(${literal})\s*\)`, 'g'),
        (_, value) => JSON.stringify(value.slice(1, -1).replace(/\\(['\\])/g, '$1'))
            .replace(/&/g, '&amp;').replace(/"/g, '&quot;'),
    );
}
