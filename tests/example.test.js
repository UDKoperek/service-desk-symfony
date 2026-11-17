import { describe, expect, it } from "vitest";
import { TestDriver } from "testdriverai/vitest/hooks";

describe("Sample Web Application Flow", () => {
  it("should load the example domain and navigate to learn more", async (context) => {
    const testdriver = TestDriver(context);

    // Provision Chrome and navigate to the target site
    await testdriver.provision.chrome({ url: "https://example.com" });

    // Assert that the page content loaded
    const assertResult = await testdriver.assert(
      "The text 'This domain is for use in documentation examples' is visible on the page"
    );
    expect(assertResult).toBeTruthy();

    // Scroll down to reveal footer links
    await testdriver.scroll("down", { amount: 300 });

    // Click on the Learn more link
    const learnMoreLink = await testdriver.find("Learn more link");
    await learnMoreLink.click();

    // Assert that navigation to IANA Example Domains page succeeded
    const assertNavResult = await testdriver.assert(
      "The IANA Example Domains page is visible"
    );
    expect(assertNavResult).toBeTruthy();
  });
});
