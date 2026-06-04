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
