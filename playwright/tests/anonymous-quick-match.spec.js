const { test, expect } = require('@playwright/test');

const ROOM_URL_REGEX = /\/phong\/([^/]+)\/(do|den)$/;

test.describe('Anonymous quick match', () => {
  test('two players join the same room', async ({ browser }) => {
    test.setTimeout(60_000);

    const [contextA, contextB] = await Promise.all([
      browser.newContext(),
      browser.newContext(),
    ]);
    const [playerA, playerB] = await Promise.all([
      contextA.newPage(),
      contextB.newPage(),
    ]);

    await Promise.all([
      playerA.goto('/sanh-cho'),
      playerB.goto('/sanh-cho'),
    ]);

    const buttonA = playerA.locator('#find-match-btn');
    const buttonB = playerB.locator('#find-match-btn');

    await Promise.all([
      expect(buttonA).toBeVisible(),
      expect(buttonB).toBeVisible(),
    ]);

    const statusA = playerA.locator('#match-status');
    const statusB = playerB.locator('#match-status');

    await buttonA.click();
    await expect(statusA).toHaveText(/Đang tìm|Đã tìm/, { timeout: 5_000 });

    await buttonB.click();
    await expect(statusB).toHaveText(/Đang tìm|Đã tìm/, { timeout: 5_000 });

    const [urlA, urlB] = await Promise.all([
      playerA.waitForURL(ROOM_URL_REGEX, { timeout: 45_000 }).then((url) => url.toString()),
      playerB.waitForURL(ROOM_URL_REGEX, { timeout: 45_000 }).then((url) => url.toString()),
    ]);

    const matchA = ROOM_URL_REGEX.exec(urlA);
    const matchB = ROOM_URL_REGEX.exec(urlB);

    expect(matchA?.[1]).toBeTruthy();
    expect(matchB?.[1]).toBeTruthy();
    expect(matchA[1]).toBe(matchB[1]); // same room
    expect(matchA[2]).not.toBe(matchB[2]); // different sides
  });
});
