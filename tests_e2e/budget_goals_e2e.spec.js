import { test, expect } from '@playwright/test';
import path from 'path';

test.use({ 
  channel: 'msedge',
  viewport: { width: 1280, height: 800 }
});

const screenshotDir = 'C:/Users/hasan/.gemini/antigravity/brain/724f5549-d802-457e-824f-11e893c51a9d/screenshots';

test.describe('Campus Coin — Budget Goals & Dashboard Browser Verification', () => {

  test('full budget lifecycle: create, edit, consume, near-limit, over-budget, dashboard, isolation, delete', async ({ page }) => {
    // ----------------------------------------------------
    // Step 1: Sign in as Alex Rivera
    // ----------------------------------------------------
    await page.goto('http://127.0.0.1:8000/login');
    await expect(page.locator('h1')).toContainText('Smart Spending');

    await page.fill('input[name="email"]', 'alex.rivera@campus.edu');
    await page.fill('input[name="password"]', 'StudentSecure123!');
    await page.click('button[type="submit"]');

    await page.waitForURL('**/dashboard');
    await expect(page.locator('h1')).toContainText('Welcome back, Alex Rivera');

    // ----------------------------------------------------
    // Step 2: Navigate to Budgets and Create a Budget
    // ----------------------------------------------------
    await page.goto('http://127.0.0.1:8000/budgets');
    await expect(page.locator('h1')).toContainText('Budget Goals');

    // Open Create Modal
    await page.click('button:has-text("Set Budget Goal")');
    await expect(page.locator('h2:has-text("Set Budget Goal")')).toBeVisible();

    // Select Food category and enter $100.00
    await page.selectOption('#form-category', { label: 'Food (System)' });
    await page.fill('#form-amount', '100.00');
    await page.click('button[type="submit"]:has-text("Set Budget")');

    // Verify budget created
    await expect(page.locator('text=Budget goal created successfully.')).toBeVisible();
    await expect(page.locator('div:has-text("Food")').first()).toBeVisible();
    await page.screenshot({ path: path.join(screenshotDir, '1_budget_created.png') });

    // ----------------------------------------------------
    // Step 3: Edit Budget Goal (Change limit to $120.00)
    // ----------------------------------------------------
    await page.click('button[title="Edit limit"]');
    await expect(page.locator('h2:has-text("Edit Budget Goal")')).toBeVisible();

    await page.fill('#form-amount', '120.00');
    await page.click('button[type="submit"]:has-text("Save Changes")');

    await expect(page.locator('text=Budget goal updated successfully.')).toBeVisible();
    await expect(page.locator('text=/ $120.00').first()).toBeVisible();
    await page.screenshot({ path: path.join(screenshotDir, '2_budget_edited.png') });

    // ----------------------------------------------------
    // Step 4: Add Expense Against Budgeted Category (Food)
    // ----------------------------------------------------
    await page.goto('http://127.0.0.1:8000/transactions');
    await expect(page.locator('h1')).toContainText('Transaction History');

    await page.click('button:has-text("Add Transaction")');
    await expect(page.locator('h2:has-text("Quick Add Transaction")')).toBeVisible();

    await page.fill('input#quick-add-amount', '70.00');
    await page.click('div[role="radiogroup"] button:has-text("Food")');
    await page.fill('input#merchant', 'Campus Dining Market');
    await page.click('button[type="submit"]:has-text("Record Transaction")');

    await expect(page.locator('text=recorded successfully.').first()).toBeVisible();

    // ----------------------------------------------------
    // Step 5: Verify Budget Consumption & Near-Limit Status (>=75%)
    // Alex had $24.50 seeded + $70.00 new = $94.50 on $120.00 limit = 78.8%
    // ----------------------------------------------------
    await page.goto('http://127.0.0.1:8000/budgets');
    await expect(page.locator('text=$94.50').first()).toBeVisible();
    await expect(page.locator('text=Near Limit').first()).toBeVisible();
    await expect(page.locator('text=78.8%').first()).toBeVisible();
    await expect(page.locator('text=Budget Notice: 1 category is nearing the monthly limit')).toBeVisible();
    await page.screenshot({ path: path.join(screenshotDir, '3_budget_near_limit.png') });

    // ----------------------------------------------------
    // Step 6: Add Another Expense to Trigger Over-Budget (>100%)
    // Add $35.00 -> Total spent $129.50 on $120.00 limit = 107.9%
    // ----------------------------------------------------
    await page.goto('http://127.0.0.1:8000/transactions');
    await page.click('button:has-text("Add Transaction")');
    await expect(page.locator('h2:has-text("Quick Add Transaction")')).toBeVisible();

    await page.fill('input#quick-add-amount', '35.00');
    await page.click('div[role="radiogroup"] button:has-text("Food")');
    await page.fill('input#merchant', 'Late Night Pizzeria');
    await page.click('button[type="submit"]:has-text("Record Transaction")');

    await expect(page.locator('text=recorded successfully.').first()).toBeVisible();

    // ----------------------------------------------------
    // Step 7: Verify Over-Budget State on /budgets
    // ----------------------------------------------------
    await page.goto('http://127.0.0.1:8000/budgets');
    await expect(page.locator('text=$129.50').first()).toBeVisible();
    await expect(page.locator('text=Over Budget').first()).toBeVisible();
    await expect(page.locator('text=107.9%').first()).toBeVisible();
    await expect(page.locator('text=-$9.50').first()).toBeVisible();
    await expect(page.locator('text=Budget Alert: 1 category has exceeded their monthly limit')).toBeVisible();
    await page.screenshot({ path: path.join(screenshotDir, '4_over_budget_detected.png') });

    // ----------------------------------------------------
    // Step 8: Verify Dashboard Reflects Budget & Alert
    // ----------------------------------------------------
    await page.goto('http://127.0.0.1:8000/dashboard');
    await expect(page.locator('text=Budget Alert: Food has exceeded monthly limit')).toBeVisible();
    await expect(page.locator('h2:has-text("Budget Goals & Spending Caps")')).toBeVisible();
    await expect(page.locator('text=$129.50').first()).toBeVisible();
    await expect(page.locator('text=107.9%').first()).toBeVisible();
    await page.screenshot({ path: path.join(screenshotDir, '5_dashboard_budget_alert.png') });

    // ----------------------------------------------------
    // Step 9: Verify Multi-Tenant Student Isolation
    // Log out Alex, log in Maria Santos. Maria should NOT see Alex's budget.
    // ----------------------------------------------------
    await page.click('button:has-text("Sign Out")');
    await page.waitForURL('**/login');

    await page.fill('input[name="email"]', 'maria.santos@campus.edu');
    await page.fill('input[name="password"]', 'StudentSecure123!');
    await page.click('button[type="submit"]');

    await page.waitForURL('**/dashboard');
    await expect(page.locator('h1')).toContainText('Welcome back, Maria Santos');

    // Maria's dashboard should have NO over-budget alert
    await expect(page.locator('text=Budget Alert: Food has exceeded monthly limit')).not.toBeVisible();
    await expect(page.locator('text=No budget goals set for')).toBeVisible();

    // Maria's budgets page should be empty
    await page.goto('http://127.0.0.1:8000/budgets');
    await expect(page.locator('text=NO BUDGET GOALS SET FOR')).toBeVisible();
    await expect(page.locator('text=$129.50')).not.toBeVisible();
    await page.screenshot({ path: path.join(screenshotDir, '6_data_isolation_verified.png') });

    // ----------------------------------------------------
    // Step 10: Delete Budget
    // Log back in as Alex and delete the Food budget
    // ----------------------------------------------------
    await page.click('button:has-text("Sign Out")');
    await page.waitForURL('**/login');

    await page.fill('input[name="email"]', 'alex.rivera@campus.edu');
    await page.fill('input[name="password"]', 'StudentSecure123!');
    await page.click('button[type="submit"]');

    await page.waitForURL('**/dashboard');
    await page.goto('http://127.0.0.1:8000/budgets');

    // Delete Food budget
    await page.click('button[title="Delete budget"]');
    await expect(page.locator('h2:has-text("Delete Budget Goal?")')).toBeVisible();
    await page.click('button:has-text("Delete Budget")');

    await expect(page.locator('text=Budget goal deleted successfully.')).toBeVisible();
    await expect(page.locator('text=NO BUDGET GOALS SET FOR')).toBeVisible();
    await page.screenshot({ path: path.join(screenshotDir, '7_budget_deleted.png') });
  });

});
