const nameCheck  = /^[-_a-zA-Z0-9]{4,22}$/;
const tokenCheck = /^[-_/+a-zA-Z0-9]{24,}$/;

// -----------------------------
// Helpers
// -----------------------------

function isHTMLFormElement(el: Element | null): el is HTMLFormElement {
    return el instanceof HTMLFormElement;
}

function isHTMLInputElement(el: Element | null): el is HTMLInputElement {
    return el instanceof HTMLInputElement;
}

// -----------------------------
// Event bindings
// -----------------------------

// Generate and double-submit a CSRF token in a form field and a cookie, as defined by Symfony's SameOriginCsrfTokenManager
document.addEventListener(
    'submit',
    (event: Event) => {
        if (event.target && isHTMLFormElement(event.target as Element)) {
            generateCsrfToken(event.target as HTMLFormElement);
        }
    },
    true
);

// When @hotwired/turbo handles form submissions, send the CSRF token in a header
document.addEventListener('turbo:submit-start', (event: Event) => {
    // Turbo events ne sont pas typés par défaut -> on triche avec `any`
    const e = event as any;
    if (e.detail?.formSubmission?.formElement instanceof HTMLFormElement) {
        const h = generateCsrfHeaders(e.detail.formSubmission.formElement);
        Object.keys(h).forEach((k) => {
            e.detail.formSubmission.fetchRequest.headers[k] = h[k];
        });
    }
});

// When @hotwired/turbo handles form submissions, remove the CSRF cookie
document.addEventListener('turbo:submit-end', (event: Event) => {
    const e = event as any;
    if (e.detail?.formSubmission?.formElement instanceof HTMLFormElement) {
        removeCsrfToken(e.detail.formSubmission.formElement);
    }
});

// -----------------------------
// Core functions
// -----------------------------

export function generateCsrfToken(formElement: HTMLFormElement): void {
    const csrfField = formElement.querySelector<HTMLInputElement>(
        'input[data-controller="csrf-protection"], input[name="_csrf_token"]'
    );

    if (!csrfField) {
        return;
    }

    let csrfCookie = csrfField.getAttribute('data-csrf-protection-cookie-value');
    let csrfToken  = csrfField.value;

    if (!csrfCookie && nameCheck.test(csrfToken)) {
        csrfField.setAttribute('data-csrf-protection-cookie-value', (csrfCookie = csrfToken));
        csrfField.defaultValue = csrfToken = btoa(
            String.fromCharCode.apply(
                null,
                Array.from(window.crypto.getRandomValues(new Uint8Array(18))) as unknown as number[]
            )
        );
    }
    csrfField.dispatchEvent(new Event('change', {bubbles: true}));

    if (csrfCookie && tokenCheck.test(csrfToken)) {
        const cookie    =
                  csrfCookie + '_' + csrfToken + '=' + csrfCookie + '; path=/; samesite=strict';
        document.cookie =
            window.location.protocol === 'https:'
            ? '__Host-' + cookie + '; secure'
            : cookie;
    }
}

export function generateCsrfHeaders(formElement: HTMLFormElement): Record<string, string> {
    const headers: Record<string, string> = {};
    const csrfField                       = formElement.querySelector<HTMLInputElement>(
        'input[data-controller="csrf-protection"], input[name="_csrf_token"]'
    );

    if (!csrfField) {
        return headers;
    }

    const csrfCookie = csrfField.getAttribute('data-csrf-protection-cookie-value');

    if (tokenCheck.test(csrfField.value) && csrfCookie && nameCheck.test(csrfCookie)) {
        headers[csrfCookie] = csrfField.value;
    }

    return headers;
}

export function removeCsrfToken(formElement: HTMLFormElement): void {
    const csrfField = formElement.querySelector<HTMLInputElement>(
        'input[data-controller="csrf-protection"], input[name="_csrf_token"]'
    );

    if (!csrfField) {
        return;
    }

    const csrfCookie = csrfField.getAttribute('data-csrf-protection-cookie-value');

    if (tokenCheck.test(csrfField.value) && csrfCookie && nameCheck.test(csrfCookie)) {
        const cookie =
                  csrfCookie +
                  '_' +
                  csrfField.value +
                  '=0; path=/; samesite=strict; max-age=0';

        document.cookie =
            window.location.protocol === 'https:'
            ? '__Host-' + cookie + '; secure'
            : cookie;
    }
}

/* stimulusFetch: 'lazy' */
export default 'csrf-protection-controller';
