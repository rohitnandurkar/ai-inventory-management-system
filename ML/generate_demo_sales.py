import mysql.connector
import random
from datetime import date, timedelta


# =========================================================
# DATABASE
# =========================================================

DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "inventory_system"
}


# =========================================================
# SETTINGS
# =========================================================

HISTORY_DAYS = 60

# Set this to True ONLY if you want to remove
# previously generated demo sales.
CLEAR_DEMO_DATA = False


# =========================================================
# DATABASE CONNECTION
# =========================================================

conn = mysql.connector.connect(**DB_CONFIG)

cursor = conn.cursor(dictionary=True)


# =========================================================
# GET PRODUCTS
# =========================================================

cursor.execute("""
    SELECT id, name
    FROM products
    ORDER BY id
""")

products = cursor.fetchall()


if not products:

    print("No products found.")

    cursor.close()
    conn.close()

    exit()


print()
print("==============================================")
print("   INVENTORY DEMO SALES GENERATOR")
print("==============================================")
print()

print(f"Products found: {len(products)}")
print(f"History period: {HISTORY_DAYS} days")
print()


# =========================================================
# OPTIONAL CLEAR
# =========================================================

if CLEAR_DEMO_DATA:

    print("Clearing demo sales...")

    cursor.execute("""
        DELETE FROM sales
        WHERE price = 0
    """)

    conn.commit()

    print("Demo sales cleared.")
    print()


# =========================================================
# CHECK EXISTING SALES
# =========================================================

cursor.execute("""
    SELECT
        product_id,
        COUNT(*) AS total_sales,
        COUNT(DISTINCT DATE(date)) AS sales_days
    FROM sales
    GROUP BY product_id
""")

existing_sales = cursor.fetchall()

existing_map = {}

for row in existing_sales:

    existing_map[
        row["product_id"]
    ] = row


# =========================================================
# GENERATE SALES
# =========================================================

today = date.today()

start_date = (
    today -
    timedelta(days=HISTORY_DAYS - 1)
)


total_records = 0


for product in products:

    product_id = product["id"]
    product_name = product["name"]


    existing = existing_map.get(
        product_id
    )


    if existing and existing["sales_days"] >= 7:

        print(
            f"SKIP: {product_name} "
            f"already has "
            f"{existing['sales_days']} sales days."
        )

        continue


    print(
        f"Generating: {product_name}"
    )


    # -----------------------------------------------------
    # Give every product a slightly different demand
    # -----------------------------------------------------

    base_demand = random.randint(
        2,
        8
    )


    # Different trend for products
    trend_type = random.choice([
        "increasing",
        "stable",
        "decreasing"
    ])


    for day_number in range(
        HISTORY_DAYS
    ):

        current_date = (
            start_date +
            timedelta(days=day_number)
        )


        # -------------------------------------------------
        # Weekend effect
        # -------------------------------------------------

        weekday = current_date.weekday()

        weekend_factor = 1.0

        if weekday >= 5:

            weekend_factor = 0.75


        # -------------------------------------------------
        # Trend
        # -------------------------------------------------

        trend_factor = 1.0


        if trend_type == "increasing":

            trend_factor = (
                1 +
                (day_number /
                 HISTORY_DAYS) *
                0.60
            )


        elif trend_type == "decreasing":

            trend_factor = (
                1.30 -
                (day_number /
                 HISTORY_DAYS) *
                0.50
            )


        # -------------------------------------------------
        # Random demand variation
        # -------------------------------------------------

        noise = random.uniform(
            0.75,
            1.25
        )


        quantity = round(
            base_demand *
            weekend_factor *
            trend_factor *
            noise
        )


        # Minimum demand
        quantity = max(
            1,
            quantity
        )


        # -------------------------------------------------
        # Insert sale
        #
        # price = 0 is intentionally used to mark
        # generated demo records.
        #
        # This does NOT change product stock.
        # -------------------------------------------------

        cursor.execute("""
            INSERT INTO sales
            (
                product_id,
                qty,
                price,
                date
            )
            VALUES
            (
                %s,
                %s,
                %s,
                %s
            )
        """, (
            product_id,
            quantity,
            0,
            current_date
        ))


        total_records += 1


# =========================================================
# COMMIT
# =========================================================

conn.commit()


# =========================================================
# RESULT
# =========================================================

print()
print("==============================================")
print("              GENERATION COMPLETE")
print("==============================================")
print()

print(
    f"Generated sales records: {total_records}"
)

print()

print(
    "Product stock quantities were NOT changed."
)

print()

print(
    "You can now run:"
)

print()

print(
    "python .\\ml_engine.py 1 7"
)

print()


cursor.close()
conn.close()