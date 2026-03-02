#!/bin/bash

# ==============================================================================
# WP-CLI Script to Generate Primary Navigation
# Run this inside your Docker container
# ==============================================================================

MENU_NAME="Primary Intranet Nav"

echo "Cleaning up old menu (if it exists)..."
wp menu delete "$MENU_NAME" > /dev/null 2>&1

echo "Creating menu: $MENU_NAME..."
wp menu create "$MENU_NAME" > /dev/null 2>&1

echo "Adding items..."

# ------------------------------------------------------------------------------
# 3. About GCA
# ------------------------------------------------------------------------------
PARENT_3_ID=$(wp menu item add-custom "$MENU_NAME" "About GCA" "#" --porcelain)
wp menu item add-custom "$MENU_NAME" "GCA Onboarding" "/gca-onboarding/" --parent-id=$PARENT_3_ID
wp menu item add-custom "$MENU_NAME" "News" "/news/" --parent-id=$PARENT_3_ID
wp menu item add-custom "$MENU_NAME" "Who we are" "/who-we-are/" --parent-id=$PARENT_3_ID
wp menu item add-custom "$MENU_NAME" "Business plan and performance" "/business-plan-and-performance/" --parent-id=$PARENT_3_ID
wp menu item add-custom "$MENU_NAME" "Our governance" "/our-governance/" --parent-id=$PARENT_3_ID

# ------------------------------------------------------------------------------
# 4. HR
# ------------------------------------------------------------------------------
PARENT_9_ID=$(wp menu item add-custom "$MENU_NAME" "HR" "#" --porcelain)
wp menu item add-custom "$MENU_NAME" "Pay and pensions" "/pay-and-pensions/" --parent-id=$PARENT_9_ID
wp menu item add-custom "$MENU_NAME" "Performance management" "/performance-management/" --parent-id=$PARENT_9_ID
wp menu item add-custom "$MENU_NAME" "Health and wellbeing" "/health-and-wellbeing/" --parent-id=$PARENT_9_ID
wp menu item add-custom "$MENU_NAME" "Leave,absence and flexible working" "/leaveabsence-and-flexible-working/" --parent-id=$PARENT_9_ID
wp menu item add-custom "$MENU_NAME" "Employee benefits" "/employee-benefits/" --parent-id=$PARENT_9_ID
wp menu item add-custom "$MENU_NAME" "Ant-fraud and corruption" "/ant-fraud-and-corruption/" --parent-id=$PARENT_9_ID
wp menu item add-custom "$MENU_NAME" "Line manager area" "/line-manager-area/" --parent-id=$PARENT_9_ID
wp menu item add-custom "$MENU_NAME" "New starters and leavers" "/new-starters-and-leavers/" --parent-id=$PARENT_9_ID
wp menu item add-custom "$MENU_NAME" "Learning and development" "/learning-and-development/" --parent-id=$PARENT_9_ID
wp menu item add-custom "$MENU_NAME" "Inclusion and diversity" "/inclusion-and-diversity/" --parent-id=$PARENT_9_ID
wp menu item add-custom "$MENU_NAME" "Respect at work" "/respect-at-work/" --parent-id=$PARENT_9_ID
wp menu item add-custom "$MENU_NAME" "Workday" "/workday/" --parent-id=$PARENT_9_ID

# ------------------------------------------------------------------------------
# 5. Workplace and travel
# ------------------------------------------------------------------------------
PARENT_22_ID=$(wp menu item add-custom "$MENU_NAME" "Workplace and travel" "#" --porcelain)
wp menu item add-custom "$MENU_NAME" "Workplace and travel" "/workplace-and-travel/" --parent-id=$PARENT_22_ID
wp menu item add-custom "$MENU_NAME" "Our offices" "/our-offices/" --parent-id=$PARENT_22_ID
wp menu item add-custom "$MENU_NAME" "Working from home" "/working-from-home/" --parent-id=$PARENT_22_ID
wp menu item add-custom "$MENU_NAME" "Travel and expenses" "/travel-and-expenses/" --parent-id=$PARENT_22_ID
wp menu item add-custom "$MENU_NAME" "Health and safety" "/health-and-safety/" --parent-id=$PARENT_22_ID

# ------------------------------------------------------------------------------
# 6. Business processes
# ------------------------------------------------------------------------------
PARENT_27_ID=$(wp menu item add-custom "$MENU_NAME" "Business processes" "#" --porcelain)
wp menu item add-custom "$MENU_NAME" "Accessbility" "/accessbility/" --parent-id=$PARENT_27_ID
wp menu item add-custom "$MENU_NAME" "Knowledge centre" "/knowledge-centre/" --parent-id=$PARENT_27_ID
wp menu item add-custom "$MENU_NAME" "Marketing and communications" "/marketing-and-communications/" --parent-id=$PARENT_27_ID
wp menu item add-custom "$MENU_NAME" "Change management" "/change-management/" --parent-id=$PARENT_27_ID
wp menu item add-custom "$MENU_NAME" "Digital, data and AI" "/digital-data-and-ai/" --parent-id=$PARENT_27_ID
wp menu item add-custom "$MENU_NAME" "Finance" "/finance/" --parent-id=$PARENT_27_ID
wp menu item add-custom "$MENU_NAME" "Customers and suppliers" "/customers-and-suppliers/" --parent-id=$PARENT_27_ID

# ------------------------------------------------------------------------------
# 7. IT, data and security
# ------------------------------------------------------------------------------
PARENT_35_ID=$(wp menu item add-custom "$MENU_NAME" "IT, data and security" "#" --porcelain)
wp menu item add-custom "$MENU_NAME" "IT support" "/it-support/" --parent-id=$PARENT_35_ID
wp menu item add-custom "$MENU_NAME" "Information security" "/information-security/" --parent-id=$PARENT_35_ID
wp menu item add-custom "$MENU_NAME" "Blogs" "/blogs/" --parent-id=$PARENT_35_ID
wp menu item add-custom "$MENU_NAME" "Work updates" "/work-updates/" --parent-id=$PARENT_35_ID
wp menu item add-custom "$MENU_NAME" "Events" "/events/" --parent-id=$PARENT_35_ID
wp menu item add-custom "$MENU_NAME" "Staff netwroks" "/staff-netwroks/" --parent-id=$PARENT_35_ID

# ------------------------------------------------------------------------------
# Assign to theme location
# ------------------------------------------------------------------------------
# Note: Swap out 'primary' with whatever your theme's registered menu location is called!
wp menu location assign "$MENU_NAME" primary

echo "✅ Menu generated successfully!"