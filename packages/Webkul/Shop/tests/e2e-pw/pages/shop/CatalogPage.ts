import { expect, Page } from "@playwright/test";
import { BasePage } from "../BasePage";

export class CatalogPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(query: string = ""): Promise<void> {
        await this.visit(`products${query}`);

        await this.page.waitForLoadState("networkidle");
    }

    /**
     * The page promises every product, so the count it reports is the assertion worth
     * making; the card elements themselves are shared with the rest of the storefront.
     */
    async resultCount(): Promise<number> {
        const summary = this.page.getByText(/Showing \d+ of \d+ products/).first();

        await expect(summary).toBeVisible();

        return Number(((await summary.textContent()) ?? "").match(/of (\d+)/)?.[1] ?? 0);
    }

    async openFilters(): Promise<void> {
        await this.page.getByRole("button", { name: /filter/i }).first().click();

        await expect(this.page.locator(".panel-side")).toBeVisible();
    }

    async expectBodyFreeOfBrowseSections(): Promise<void> {
        const body = await this.page.evaluate(
            () => document.body.innerText.split(/Showing \d+ of/)[0],
        );

        expect(body).not.toContain("Browse by Category");
        expect(body).not.toContain("Browse by Specification");
    }

    async expectBrowseSectionsInDialog(): Promise<void> {
        await expect(this.page.getByText("Browse by Category").first()).toBeVisible();
        await expect(this.page.getByText("Browse by Specification").first()).toBeVisible();
    }

    async toggleCategory(name: RegExp): Promise<void> {
        await this.page.getByRole("button", { name }).first().click();
    }

    async toggleSpecChip(name: RegExp): Promise<void> {
        await this.page.getByRole("button", { name }).first().click();
    }

    async tickPanelOption(label: string): Promise<void> {
        await this.page.locator(".panel-side").getByText(label, { exact: true }).first().click();
    }

    async pressedChipLabels(): Promise<string[]> {
        return this.page.$$eval('button[aria-pressed="true"]', (elements) =>
            elements.map((el) => (el.textContent ?? "").replace(/\s+/g, " ").trim()),
        );
    }

    async expectQueryParam(name: string): Promise<void> {
        await expect(this.page).toHaveURL(new RegExp(`[?&]${name}=`));
    }

    async expectNoHorizontalScroll(): Promise<void> {
        const overflows = await this.page.evaluate(
            () => document.documentElement.scrollWidth > document.documentElement.clientWidth,
        );

        expect(overflows).toBe(false);
    }
}
