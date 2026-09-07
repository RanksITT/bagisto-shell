import { test, expect } from "../setup";
import { CatalogPage } from "../pages/shop/CatalogPage";

test("should list the whole catalogue without paging", async ({ shopPage }) => {
    const catalogPage = new CatalogPage(shopPage);

    await catalogPage.goto();

    expect(await catalogPage.resultCount()).toBeGreaterThan(0);

    await expect(shopPage.getByRole("button", { name: /load more/i })).toHaveCount(0);
});

test("should lay the catalogue out without a horizontal scroll", async ({ shopPage }) => {
    const catalogPage = new CatalogPage(shopPage);

    await catalogPage.goto();
    await catalogPage.expectNoHorizontalScroll();
});

test("should keep browsing in the dialog rather than down the page", async ({ shopPage }) => {
    const catalogPage = new CatalogPage(shopPage);

    await catalogPage.goto();
    await catalogPage.expectBodyFreeOfBrowseSections();

    await catalogPage.openFilters();
    await catalogPage.expectBrowseSectionsInDialog();
});

test("should stack a specification chip and a category without closing the dialog", async ({
    shopPage,
}) => {
    const catalogPage = new CatalogPage(shopPage);

    await catalogPage.goto();

    const total = await catalogPage.resultCount();

    await catalogPage.openFilters();

    await catalogPage.toggleSpecChip(/^Shell\s+\d+$/);
    await catalogPage.expectQueryParam("brand");

    const filtered = await catalogPage.resultCount();

    expect(filtered).toBeLessThan(total);

    await catalogPage.toggleCategory(/Motorcycle Engine Oil/);
    await catalogPage.expectQueryParam("category_id");

    expect(await catalogPage.resultCount()).toBeLessThanOrEqual(filtered);

    await expect(shopPage.locator(".panel-side")).toBeVisible();
});

test("should keep a chip and its tick box showing the same thing", async ({ shopPage }) => {
    const catalogPage = new CatalogPage(shopPage);

    await catalogPage.goto();
    await catalogPage.openFilters();

    await catalogPage.tickPanelOption("Prestone");

    await expect
        .poll(async () => await catalogPage.pressedChipLabels(), { timeout: 20000 })
        .toContainEqual(expect.stringContaining("Prestone"));
});
