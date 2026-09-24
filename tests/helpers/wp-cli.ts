import { execSync } from 'child_process';
import * as path from 'path';

const PROJECT_ROOT = path.resolve(__dirname, '../../');

function run(cmd: string): string {
    return execSync(cmd, {
        encoding: 'utf8',
        cwd: PROJECT_ROOT,
        stdio: ['pipe', 'pipe', 'pipe'],
    }).trim();
}

export function wpCli(subCmd: string): string {
    return run(`docker compose exec -T wordpress wp ${subCmd} --allow-root`);
}

export function getUserId(login: string): number {
    const out = wpCli(`user get ${login} --field=ID`);
    const id = parseInt(out, 10);
    if (!id) throw new Error(`wp user get failed for "${login}": ${out}`);
    return id;
}

export function createPost(
    title: string,
    status: 'draft' | 'publish' | 'pending' | 'gca_archived' = 'draft',
    type = 'page',
    authorLogin?: string,
): number {
    const escaped = title.replace(/"/g, '\\"');
    const authorArg = authorLogin ? ` --post_author=${getUserId(authorLogin)}` : '';
    const out = wpCli(`post create --post_type=${type} --post_title="${escaped}" --post_status=${status}${authorArg} --porcelain`);
    const id = parseInt(out, 10);
    if (!id) throw new Error(`wp post create failed for "${title}": ${out}`);
    return id;
}

export function getPostStatus(id: number): string {
    return wpCli(`post get ${id} --field=post_status`);
}

export function setPostStatus(id: number, status: string): void {
    wpCli(`post update ${id} --post_status=${status}`);
}

export function deletePost(id: number): void {
    wpCli(`post delete ${id} --force`);
}

// WordPress stores a heartbeat-based editor lock in _edit_lock post meta.
// Deleting it before opening the editor prevents the "post is currently being
// edited" dialog that appears when a previous browser context left a lock.
export function deletePostLock(id: number): void {
    try {
        wpCli(`post meta delete ${id} _edit_lock`);
    } catch { /* ignore — lock may not be set */ }
}

export function setPostTerms(postId: number, termIds: number[], taxonomy: string): void {
    wpCli(`post term set ${postId} ${taxonomy} ${termIds.join(' ')} --by=id`);
}

/**
 * Assign responsible_team directorates to a contributor via user meta.
 * Pass 'all' for unrestricted access, or an array of term IDs to scope them.
 */
export function setContributorTeams(userLogin: string, teamIds: number[] | 'all'): void {
    const userId = getUserId(userLogin);
    const value  = teamIds === 'all'
        ? JSON.stringify(['all'])
        : JSON.stringify(teamIds.map(Number));
    wpCli(`user meta update ${userId} _gca_contributor_teams '${value}' --format=json`);
}

/**
 * Returns up to `count` term IDs from the responsible_team taxonomy.
 * Throws if fewer than `count` terms exist.
 */
export function getResponsibleTeamTermIds(count = 2): number[] {
    const out = wpCli(`term list responsible_team --field=term_id --format=csv --number=${count}`);
    const ids  = out.split('\n').map(s => parseInt(s.trim(), 10)).filter(id => id > 0);
    if (ids.length < count) {
        throw new Error(`Need ${count} responsible_team terms but found ${ids.length}`);
    }
    return ids;
}

/**
 * Read a gca_feature_flags entry directly from the DB, bypassing the UI.
 * Returns undefined if the flag key isn't set (falls back to its registered default).
 */
export function getFeatureFlag(id: string): boolean | undefined {
    const out = wpCli(`eval "\\$flags = get_option('gca_feature_flags', []); echo array_key_exists('${id}', \\$flags) ? (\\$flags['${id}'] ? '1' : '0') : 'unset';"`);
    if (out === 'unset') return undefined;
    return out === '1';
}

/**
 * Set (or unset) a gca_feature_flags entry directly via the DB, bypassing the UI.
 * Passing undefined removes the key entirely, restoring the registered default.
 */
export function setFeatureFlag(id: string, value: boolean | undefined): void {
    if (value === undefined) {
        wpCli(`eval "\\$flags = get_option('gca_feature_flags', []); unset(\\$flags['${id}']); update_option('gca_feature_flags', \\$flags);"`);
        return;
    }
    wpCli(`eval "\\$flags = get_option('gca_feature_flags', []); \\$flags['${id}'] = ${value ? 'true' : 'false'}; update_option('gca_feature_flags', \\$flags);"`);
}
