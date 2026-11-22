/// <reference types="node" />
import { execFileSync } from 'node:child_process';
import { existsSync } from 'node:fs';
import { e2eEnv } from '../../../playwright.config';

/** Builds the isolated e2e database before any test runs. */
export default function globalSetup(): void {
    if (existsSync('public/hot')) {
        throw new Error(
            'public/hot exists: stop `npm run dev` so the e2e server uses the built assets.',
        );
    }

    if (!existsSync('public/build/manifest.json')) {
        throw new Error('No built assets: run `npm run build` first.');
    }

    execFileSync('php', ['tests/e2e/support/prepare-database.php'], {
        stdio: 'inherit',
        env: { ...process.env, ...e2eEnv },
    });
}
