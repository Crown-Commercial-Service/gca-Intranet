# Manual Test Checklist — Publishing Workflow (WEB-4140)

Covers the Draft → Review → Publish feature end-to-end. Mirrors the automated
Playwright suite (`tests/e2e/`) so anything ticked here has an equivalent
automated check you can also run for regression coverage — the test IDs below
match the `describe`/`test` names in that suite.

**Test accounts** (from `.env`) — emails are Gmail-style `+alias` addresses
routed to Jatin's real inbox (`jatin.patel@gca.gov.uk`) via the environment's
live SendGrid SMTP, so workflow notification emails (WF-6.x) can actually be
checked, not just verified as "triggered":

| Role | Username | Password | Email |
|---|---|---|---|
| Contributor | `test-contributor` | `Test-Contributor-1!` | `jatin.patel+test-contributor-local@gca.gov.uk` |
| Publisher | `test-publisher` | `Test-Publisher-1!` | `jatin.patel+test-publisher-local@gca.gov.uk` |
| Community Host | `test-community-host` | `Test-ComHost-1!` | `jatin.patel+test-community-host-local@gca.gov.uk` |
| Admin (setup only) | `admin` | `dr0w55ap` | `techops-cloudengineers@crowncommercial.gov.uk` |

Reviewer notification address (separate from the publisher's own login email —
this is what actually receives submit-for-review / retirement-request mails):
`jatin.patel+test-reviewer-local@gca.gov.uk`

**Known issue going in:** WF-3.1/3.2 — a contributor's *first* open of a
freshly-published page occasionally hangs on load (30s+). Every other step of
that flow works. If you hit a slow/blank editor on a brand-new live page, try
a reload before treating it as a new bug.

---

## 0. Setup / feature flag

- [ ] **WF-0.1** — `publishing-workflow` flag appears on the Feature Flags settings page
- [ ] **WF-0.2** — Enabling the flag makes *Settings → Publishing Workflow* accessible
- [ ] **WF-0.3** — *Settings → Publishing Workflow* menu item stays visible even when the flag is off (so reviewer email can be configured before enabling)

## 1. Happy path — Draft → Review → Publish (WF-1.x)

- [X] **WF-1.1** — Contributor can open an existing draft page for editing
- [X] **WF-1.2** — Contributor sees "Submit for Review" button, never "Publish"
- [X] **WF-1.3** — Submitting for review sets status to **Pending**
- [ ] **WF-1.4** — Pending page appears in the publisher's dashboard/page list
- [ ] **WF-1.5** — Publisher can approve and publish the pending page
- [ ] **WF-1.6** — Published page is visible on the live frontend

## 2. Rejection & resubmission (WF-2.x)

- [ ] **WF-2.1** — Publisher sees a "Rejection Comments" box on a Pending page
- [ ] **WF-2.2** — Submitting a rejection reverts the page to **Draft**
- [ ] **WF-2.3** — Rejection comments are saved and visible on reload
- [ ] **WF-2.4** — Contributor sees the reviewer's feedback on the rejected draft
- [ ] **WF-2.5** — Contributor resubmits → status returns to **Pending**
- [ ] **WF-2.6** — Resubmitted page shows no stale/leftover rejection feedback

## 3. Editing already-published pages — revisions (WF-3.x)

- [ ] **WF-3.1** — Contributor opens a live (published) page and the revision UI loads *(known flaky — see note above)*
- [ ] **WF-3.2** — Contributor saves a draft revision without touching the live page (frontend unchanged)
- [ ] **WF-3.3** — Contributor submits the revision for review
- [ ] **WF-3.4** — Publisher sees a "Compare Revision" option
- [ ] **WF-3.5** — Publisher approves the revision → live content updates
- [ ] **WF-3.6** — Approving a revision updates the page's Last Modified date
- [ ] **WF-3.7** — Publisher can reject a revision with comments (live page stays unchanged)

## 4. Deletion & retirement (WF-4.x)

- [ ] **WF-4.1** — Contributor cannot delete a Draft page; sees a retirement-request notice instead
- [ ] **WF-4.2** — Contributor cannot trash a Published page
- [ ] **WF-4.3** — Publisher can trash a Published page
- [ ] **WF-4.4** — Publisher can permanently delete a trashed page
- [ ] **WF-4.5** — Publisher can set a Published page's status to **Archived**
- [ ] **WF-4.6** — An Archived page returns 404 on the public frontend

## 5. Email notifications (WF-6.x)

- [ ] **WF-6.1** — Submitting for review (status → Pending) triggers a reviewer notification
- [ ] **WF-6.2** — Revision-submitted email subject reads "Update submitted for … for review"
- [ ] **WF-6.3** — Publishing a page triggers a notification back to the contributor
- [ ] **WF-6.4** — Rejecting a page triggers a notification containing the rejection comments
- [ ] **WF-6.5** — A retirement request triggers a reviewer notification

## 6. Directorate / category-scoped permissions (CAT-1.x)

- [ ] **CAT-1.1** — Publisher sees a "Content Permissions" accordion on a contributor's profile
- [ ] **CAT-1.2** — That accordion is **not** shown on a publisher's own profile
- [ ] **CAT-1.3** — Assigning a directorate to a contributor persists after reload
- [ ] **CAT-1.4** — Contributor only sees pages from their assigned directorate in the page list
- [ ] **CAT-1.5** — An in-scope page not authored by the contributor still appears under "All"
- [ ] **CAT-1.6** — Page-list view-filter counts reflect the directorate scope, not sitewide totals
- [ ] **CAT-1.7** — Contributor cannot open the edit URL for an out-of-scope page directly
- [ ] **CAT-1.8** — A contributor set to "All directorates/teams" sees every page
- [ ] **CAT-1.9** — A publisher (not just admin) can also see/use the Content Permissions accordion

## 7. Contributor role boundaries (PERM-1.x)

- [ ] **PERM-1.1** — Contributor can log into wp-admin
- [ ] **PERM-1.2** — No "Add New Page" option in their admin menu
- [ ] **PERM-1.3** — Cannot reach the New Page URL directly
- [ ] **PERM-1.4** — Can open an existing page for editing
- [ ] **PERM-1.5** — Sees "Submit for Review", never "Publish"
- [ ] **PERM-1.6** — Cannot edit standard Posts
- [ ] **PERM-1.7** — Cannot access Settings
- [ ] **PERM-1.8** — Cannot access Users
- [ ] **PERM-1.9** — Cannot access Plugins
- [ ] **PERM-1.10** — Cannot access Appearance/Themes
- [ ] **PERM-1.11** — Cannot edit the homepage
- [ ] **PERM-1.12** — Can use the Media Library and upload files

## 8. Publisher role boundaries (PERM-2.x)

- [ ] **PERM-2.1** — Publisher can access the Pages list
- [ ] **PERM-2.2** — Publisher can create a new Page
- [ ] **PERM-2.3** — Publisher sees the Publish button
- [ ] **PERM-2.4** — Publisher can delete any page (not just their own)
- [ ] **PERM-2.5** — Publisher can access the News CPT and publish
- [ ] **PERM-2.6** — Publisher cannot access Settings → Feature Flags
- [ ] **PERM-2.7** — Publisher cannot manage Users
- [ ] **PERM-2.8** — Publisher can see Pending pages submitted by contributors
- [ ] **PERM-2.9** — Publisher sees the Rejection Comments meta box on the edit screen

## 9. Retired role cleanup (PERM-4.x)

Confirms the old `editor` / `author` / `contributor` (core) / `publisher-admin`
roles are fully gone from user-facing UI — only **Contributor** and
**Publisher** should remain.

- [ ] **PERM-4.1** — No users listed under the legacy Editor role
- [ ] **PERM-4.2** — No users listed under the legacy Author role
- [ ] **PERM-4.3** — No users listed under the legacy core Contributor role
- [ ] **PERM-4.4** — "GCA Publisher" role exists and shows in the role filter
- [ ] **PERM-4.5** — Legacy Editor role is renamed with a `[Deprecated]` prefix
- [ ] **PERM-4.6** — GCA custom roles appear in the user list's role-filter tabs; **no `gca_publisher_admin` tab appears anywhere** (that role has been retired)

---

## Sign-off

- [ ] All sections above tested against the brief (WEB-4140)
- [ ] Any failures logged with steps to reproduce
- [ ] Automated suite re-run and green (excluding known WF-3.1/3.2 flake): `npx playwright test --config=playwright.chrome.config.ts`
