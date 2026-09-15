import sys
import json
import mysql.connector
import pandas as pd
import numpy as np

from datetime import timedelta
from sklearn.linear_model import LinearRegression


# ============================================================
# DATABASE CONFIGURATION
# ============================================================

DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "inventory_system"
}


# ============================================================
# DATABASE CONNECTION
# ============================================================

def get_connection():
    return mysql.connector.connect(
        host=DB_CONFIG["host"],
        user=DB_CONFIG["user"],
        password=DB_CONFIG["password"],
        database=DB_CONFIG["database"]
    )


# ============================================================
# GET PRODUCT INFORMATION
# ============================================================

def get_product(product_id):

    conn = get_connection()
    cursor = conn.cursor(dictionary=True)

    query = """
        SELECT
            id,
            name,
            quantity,
            buy_price,
            sale_price
        FROM products
        WHERE id = %s
    """

    cursor.execute(query, (product_id,))
    product = cursor.fetchone()

    cursor.close()
    conn.close()

    return product


# ============================================================
# GET SALES HISTORY
# ============================================================

def get_sales_history(product_id):

    conn = get_connection()
    cursor = conn.cursor(dictionary=True)

    query = """
        SELECT
            DATE(date) AS sale_date,
            SUM(qty) AS quantity
        FROM sales
        WHERE product_id = %s
        GROUP BY DATE(date)
        ORDER BY sale_date
    """

    cursor.execute(query, (product_id,))
    rows = cursor.fetchall()

    cursor.close()
    conn.close()

    return rows


# ============================================================
# PREPARE LAST 60 DAYS OF DATA
# ============================================================

def prepare_sales_data(rows):

    if not rows:
        return None

    df = pd.DataFrame(rows)

    df["sale_date"] = pd.to_datetime(df["sale_date"])
    df["quantity"] = pd.to_numeric(
        df["quantity"],
        errors="coerce"
    ).fillna(0)

    df = df.sort_values("sale_date")

    # --------------------------------------------------------
    # IMPORTANT:
    # Only use the latest 60 days.
    #
    # This prevents very old sales from creating a huge
    # zero-demand period.
    # --------------------------------------------------------

    latest_date = df["sale_date"].max()

    history_start = latest_date - timedelta(days=59)

    df = df[
        df["sale_date"] >= history_start
    ].copy()

    # --------------------------------------------------------
    # Create continuous daily date range
    # Missing sales days become zero demand.
    # --------------------------------------------------------

    date_range = pd.date_range(
        start=history_start,
        end=latest_date,
        freq="D"
    )

    df = (
        df.set_index("sale_date")
        .reindex(date_range, fill_value=0)
        .rename_axis("sale_date")
        .reset_index()
    )

    df["quantity"] = df["quantity"].astype(float)

    return df


# ============================================================
# TRAIN LINEAR REGRESSION
# ============================================================

def train_model(df):

    # Day numbers:
    # 0, 1, 2, 3...
    X = np.arange(
        len(df)
    ).reshape(-1, 1)

    y = df["quantity"].values

    model = LinearRegression()

    model.fit(X, y)

    return model


# ============================================================
# PREDICT FUTURE DEMAND
# ============================================================

def predict_future(model, history_length, forecast_days):

    future_X = np.arange(
        history_length,
        history_length + forecast_days
    ).reshape(-1, 1)

    predictions = model.predict(future_X)

    # Demand cannot be negative
    predictions = np.maximum(
        predictions,
        0
    )

    return predictions


# ============================================================
# HISTORICAL PREDICTIONS
# ============================================================

def get_historical_predictions(model, df):

    X = np.arange(
        len(df)
    ).reshape(-1, 1)

    predictions = model.predict(X)

    predictions = np.maximum(
        predictions,
        0
    )

    return predictions


# ============================================================
# DETERMINE DEMAND TREND
# ============================================================

def get_trend(model):

    slope = float(
        model.coef_[0]
    )

    if slope > 0.05:
        trend = "Increasing"

    elif slope < -0.05:
        trend = "Decreasing"

    else:
        trend = "Stable"

    return {
        "slope": round(slope, 4),
        "trend": trend
    }


# ============================================================
# PRODUCT PERFORMANCE
# ============================================================

def get_performance(df, model):

    total_sales = float(
        df["quantity"].sum()
    )

    average_daily_demand = float(
        df["quantity"].mean()
    )

    trend_data = get_trend(model)

    slope = trend_data["slope"]

    # --------------------------------------------------------
    # Simple college-level performance classification
    # --------------------------------------------------------

    if total_sales >= 250:

        performance = "Excellent"

    elif total_sales >= 120:

        performance = "Good"

    elif total_sales >= 50:

        performance = "Average"

    else:

        performance = "Low"

    return {
        "total_sales": round(total_sales, 2),
        "average_daily_demand": round(
            average_daily_demand,
            2
        ),
        "performance": performance,
        "trend": trend_data["trend"],
        "slope": slope
    }


# ============================================================
# STOCK RISK
# ============================================================

def calculate_stock_risk(
    current_stock,
    predicted_daily_demand
):

    current_stock = float(
        current_stock
    )

    predicted_daily_demand = float(
        predicted_daily_demand
    )

    # --------------------------------------------------------
    # Safety stock:
    # approximately 3 days of expected demand
    # --------------------------------------------------------

    safety_stock = max(
        predicted_daily_demand * 3,
        2
    )

    # --------------------------------------------------------
    # Lead time:
    # Assume 4 days for college project/demo.
    # --------------------------------------------------------

    lead_time_days = 4

    reorder_point = (
        predicted_daily_demand
        * lead_time_days
    ) + safety_stock

    # --------------------------------------------------------
    # Stock coverage
    # --------------------------------------------------------

    if predicted_daily_demand > 0:

        stock_days = (
            current_stock
            / predicted_daily_demand
        )

    else:

        stock_days = 999

    # --------------------------------------------------------
    # Risk classification
    # --------------------------------------------------------

    if current_stock <= 0:

        risk = "Out of Stock"

    elif current_stock <= reorder_point * 0.5:

        risk = "Critical"

    elif current_stock <= reorder_point:

        risk = "Low"

    elif current_stock <= reorder_point * 1.5:

        risk = "Medium"

    else:

        risk = "Healthy"

    # --------------------------------------------------------
    # Recommended order quantity
    # --------------------------------------------------------

    recommended_order = max(
        0,
        int(
            round(
                reorder_point
                - current_stock
            )
        )
    )

    return {
        "current_stock": int(current_stock),
        "predicted_daily_demand": round(
            predicted_daily_demand,
            2
        ),
        "safety_stock": round(
            safety_stock,
            2
        ),
        "lead_time_days": lead_time_days,
        "reorder_point": round(
            reorder_point,
            2
        ),
        "stock_days": round(
            stock_days,
            1
        ),
        "risk": risk,
        "recommended_order": recommended_order
    }


# ============================================================
# MAIN PRODUCT ANALYSIS
# ============================================================

def analyze_product(
    product_id,
    forecast_days=7
):

    # --------------------------------------------------------
    # Get product
    # --------------------------------------------------------

    product = get_product(
        product_id
    )

    if not product:

        return {
            "success": False,
            "message": "Product not found."
        }

    # --------------------------------------------------------
    # Get sales
    # --------------------------------------------------------

    sales_rows = get_sales_history(
        product_id
    )

    if not sales_rows:

        return {
            "success": False,
            "message": "No sales history available."
        }

    # --------------------------------------------------------
    # Prepare data
    # --------------------------------------------------------

    df = prepare_sales_data(
        sales_rows
    )

    if df is None or len(df) < 7:

        return {
            "success": False,
            "message": "At least 7 days of sales history are required."
        }

    # --------------------------------------------------------
    # Train Linear Regression
    # --------------------------------------------------------

    model = train_model(
        df
    )

    # --------------------------------------------------------
    # Historical predictions
    # --------------------------------------------------------

    historical_predictions = (
        get_historical_predictions(
            model,
            df
        )
    )

    # --------------------------------------------------------
    # Future predictions
    # --------------------------------------------------------

    future_predictions = predict_future(
        model,
        len(df),
        forecast_days
    )

    # --------------------------------------------------------
    # Average predicted demand
    # --------------------------------------------------------

    predicted_daily_demand = float(
        np.mean(
            future_predictions
        )
    )

    # --------------------------------------------------------
    # Trend
    # --------------------------------------------------------

    trend = get_trend(
        model
    )

    # --------------------------------------------------------
    # Performance
    # --------------------------------------------------------

    performance = get_performance(
        df,
        model
    )

    # --------------------------------------------------------
    # Stock risk
    # --------------------------------------------------------

    stock = calculate_stock_risk(
        product["quantity"],
        predicted_daily_demand
    )

    # --------------------------------------------------------
    # Historical graph data
    # --------------------------------------------------------

    historical_data = []

    for i in range(
        len(df)
    ):

        historical_data.append({
            "date": df.iloc[i]["sale_date"].strftime(
                "%Y-%m-%d"
            ),
            "actual": round(
                float(
                    df.iloc[i]["quantity"]
                ),
                2
            ),
            "predicted": round(
                float(
                    historical_predictions[i]
                ),
                2
            )
        })

    # --------------------------------------------------------
    # Future graph data
    # --------------------------------------------------------

    future_data = []

    last_date = df["sale_date"].max()

    for i in range(
        forecast_days
    ):

        future_date = (
            last_date
            + timedelta(
                days=i + 1
            )
        )

        future_data.append({
            "date": future_date.strftime(
                "%Y-%m-%d"
            ),
            "predicted": round(
                float(
                    future_predictions[i]
                ),
                2
            )
        })

    # --------------------------------------------------------
    # Final result
    # --------------------------------------------------------

    return {
        "success": True,

        "product": {
            "id": int(
                product["id"]
            ),
            "name": product["name"],
            "stock": int(
                product["quantity"]
            ),
            "buy_price": float(
                product["buy_price"]
            ),
            "sale_price": float(
                product["sale_price"]
            )
        },

        "model": {
            "name": "Linear Regression",
            "history_days": len(df),
            "forecast_days": forecast_days
        },

        "forecast": {
            "predicted_daily_demand": round(
                predicted_daily_demand,
                2
            ),
            "future_predictions": [
                round(
                    float(x),
                    2
                )
                for x in future_predictions
            ]
        },

        "trend": trend,

        "performance": performance,

        "stock": stock,

        "historical": historical_data,

        "future": future_data
    }


# ============================================================
# ANALYZE ALL PRODUCTS
# ============================================================

def analyze_all_products():

    conn = get_connection()
    cursor = conn.cursor(dictionary=True)

    query = """
        SELECT
            id,
            name,
            quantity
        FROM products
        ORDER BY name
    """

    cursor.execute(query)

    products = cursor.fetchall()

    cursor.close()
    conn.close()

    results = []

    for product in products:

        product_id = int(
            product["id"]
        )

        try:

            result = analyze_product(
                product_id,
                7
            )

            if result.get("success"):

                results.append({
                    "id": product_id,
                    "name": product["name"],
                    "stock": result["stock"]["current_stock"],
                    "predicted_demand": result["stock"]["predicted_daily_demand"],
                    "risk": result["stock"]["risk"],
                    "reorder_point": result["stock"]["reorder_point"],
                    "recommended_order": result["stock"]["recommended_order"],
                    "performance": result["performance"]["performance"],
                    "trend": result["performance"]["trend"],
                    "total_sales": result["performance"]["total_sales"],
                    "average_daily_demand": result["performance"]["average_daily_demand"]
                })

        except Exception as e:

            results.append({
                "id": product_id,
                "name": product["name"],
                "stock": int(
                    product["quantity"]
                ),
                "predicted_demand": 0,
                "risk": "No Data",
                "reorder_point": 0,
                "recommended_order": 0,
                "performance": "No Data",
                "trend": "Unknown",
                "total_sales": 0,
                "average_daily_demand": 0,
                "error": str(e)
            })

    # --------------------------------------------------------
    # Count risk categories
    # --------------------------------------------------------

    low_stock_count = 0
    reorder_count = 0

    for item in results:

        if item["risk"] in [
            "Critical",
            "Low",
            "Out of Stock"
        ]:

            low_stock_count += 1

        if item["recommended_order"] > 0:

            reorder_count += 1

    return {
        "success": True,
        "products": results,
        "summary": {
            "total_products": len(results),
            "low_stock": low_stock_count,
            "reorder_products": reorder_count
        }
    }


# ============================================================
# COMMAND LINE INTERFACE
# ============================================================

def main():

    try:

        # ----------------------------------------------------
        # No arguments
        # ----------------------------------------------------

        if len(sys.argv) == 1:

            result = analyze_all_products()

            print(
                json.dumps(
                    result,
                    indent=2
                )
            )

            return

        # ----------------------------------------------------
        # Product ID
        # ----------------------------------------------------

        product_id = int(
            sys.argv[1]
        )

        # ----------------------------------------------------
        # Product ID 0 = Analyze all products
        # ----------------------------------------------------

        if product_id == 0:

            result = analyze_all_products()

            print(
                json.dumps(
                    result,
                    indent=2
                )
            )

            return

        # ----------------------------------------------------
        # Forecast days
        # ----------------------------------------------------

        forecast_days = 7

        if len(sys.argv) >= 3:

            forecast_days = int(
                sys.argv[2]
            )

        # Prevent invalid forecast
        if forecast_days < 1:

            forecast_days = 1

        if forecast_days > 30:

            forecast_days = 30

        # ----------------------------------------------------
        # Analyze selected product
        # ----------------------------------------------------

        result = analyze_product(
            product_id,
            forecast_days
        )

        print(
            json.dumps(
                result,
                indent=2
            )
        )

    except Exception as e:

        error_result = {
            "success": False,
            "message": str(e)
        }

        print(
            json.dumps(
                error_result,
                indent=2
            )
        )


# ============================================================
# RUN
# ============================================================

if __name__ == "__main__":

    main()