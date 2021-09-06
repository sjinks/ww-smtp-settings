import { terser } from 'rollup-plugin-terser';

export default (async () => ({
    input: 'assets/settings.js',
    output: {
        file: 'assets/settings.min.js',
        format: 'iife',
        plugins: [
            terser(),
        ],
        compact: true,
        sourcemap: 'hidden',
        strict: false,
    },
    strictDeprecations: true,
}))();
