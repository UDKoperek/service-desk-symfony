import { describe, expect, it } from "vitest";
import { TestDriver } from "testdriverai/vitest/hooks";

/**
 * Service Desk Pro - Production / Staging E2E Test Suite
 *
 * This test suite targets the Symfony Service Desk application using the credentials
 * defined in src/DataFixtures/AppFixtures.php:
 *
 * - Admin: username "admin", password "password" (ROLE_ADMIN)
 * - Agent: username "agent.com", password "password" (ROLE_AGENT)
 *
 * To run this test suite against your deployed environment:
 *   APP_URL=https://your-production-domain.com npx vitest run tests/service-desk.sample.js
 */
const APP_URL = process.env.APP_URL || "http://localhost:8000";

describe("Service Desk Pro - Application Flows", () => {
  it("should navigate to login page and authenticate with admin credentials", async (context) => {
    const testdriver = TestDriver(context);

    // 1. Navigate to the login route
    await testdriver.provision.chrome({ url: `${APP_URL}/login` });

    // 2. Verify login form is displayed
    const loginForm = await testdriver.assert(
      "The login form with Username and Password fields is visible"
    );
    expect(loginForm).toBeTruthy();

    // 3. Fill in admin credentials from AppFixtures.php
    const usernameInput = await testdriver.find("Username input field");
    await usernameInput.click();
    await testdriver.type("admin");

    const passwordInput = await testdriver.find("Password input field");
    await passwordInput.click();
    await testdriver.type("password");

    // 4. Click Sign in button
    const submitButton = await testdriver.find("Sign in button");
    await submitButton.click();

    // 5. Assert successful authentication (greeting 'Witaj, admin!' or tickets list)
    const authenticated = await testdriver.assert(
      "The user is authenticated and welcome greeting or tickets list is displayed"
    );
    expect(authenticated).toBeTruthy();
  });

  it("should view tickets list as an anonymous user", async (context) => {
    const testdriver = TestDriver(context);

    // Navigate to the tickets list route
    await testdriver.provision.chrome({ url: `${APP_URL}/ticket` });

    // Assert that the ticket list and navigation header are displayed
    const ticketsVisible = await testdriver.assert(
      "The tickets table or page header is visible"
    );
    expect(ticketsVisible).toBeTruthy();
  });
});
