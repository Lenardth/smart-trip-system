const params = new URLSearchParams(window.location.search);
    if (params.has('auto_print')) {
        window.onload = () => setTimeout(() => window.print(), 500);
    }