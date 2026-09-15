# 🤖 AI Inventory Management System

An AI-powered inventory management system built with PHP, MySQL, and Python.

The system extends a traditional inventory management application with Linear Regression-based demand forecasting, stock health analysis, low-stock detection, and intelligent reorder recommendations.

---

## 🚀 Features

### 📦 Inventory Management

- Product management
- Category management
- User management
- Sales management
- Automatic stock deduction when a sale is created
- Stock restoration when a sale is deleted
- Correct stock adjustment when a sale is edited
- Sales reports
- Daily sales reports
- Monthly sales reports
- Yearly sales reports

### 🤖 AI Inventory Analytics

- Linear Regression demand prediction
- Historical sales analysis
- Future demand forecasting
- Actual vs Predicted Sales graph
- Product performance analysis
- Demand trend analysis
- Stock coverage calculation
- Stock risk detection
- Intelligent low-stock dashboard
- Reorder point calculation
- Recommended reorder quantity
- Fast-moving product analysis
- Slow-moving product analysis
- AI inventory recommendations

---

# 🛠️ Technology Stack

### Backend

- PHP
- MySQL
- Apache

### Machine Learning

- Python
- Pandas
- NumPy
- Scikit-learn
- Linear Regression

### Frontend

- HTML
- CSS
- Bootstrap
- JavaScript
- Chart.js

### Development Environment

- XAMPP
- Git
- GitHub

---

# 🏗️ System Architecture

```text
                  INVENTORY MANAGEMENT SYSTEM
                              |
             +----------------+----------------+
             |                                 |
        PHP Application                   MySQL Database
             |                                 |
             |                            Products
             |                            Categories
             |                            Sales
             |                            Users
             |                                 |
             +----------------+----------------+
                              |
                        Sales History
                              |
                              v
                       Python ML Engine
                              |
                     Daily Sales Demand
                              |
                              v
                     Linear Regression
                              |
                              v
                     Demand Prediction
                              |
             +----------------+----------------+
             |                |                |
             v                v                v
        Stock Risk     Stock Coverage       Trend
             |                |                |
             +----------------+----------------+
                              |
                              v
                       Reorder Point
                              |
                              v
                    Recommended Order Qty
                              |
                              v
                    AI Inventory Dashboard
```

---

# 🧠 How the AI System Works

The AI module uses historical sales data to predict future product demand.

The workflow is:

```text
Historical Sales Data
        ↓
Data Cleaning
        ↓
Daily Sales Aggregation
        ↓
Linear Regression Model
        ↓
Demand Prediction
        ↓
Stock Health Analysis
        ↓
Reorder Point Calculation
        ↓
Recommended Reorder Quantity
        ↓
AI Inventory Dashboard
```

The system analyzes recent sales history and estimates the expected daily demand for each product.

---

# 📈 Linear Regression Demand Prediction

The project uses **Linear Regression** as the machine learning algorithm.

No Random Forest, XGBoost, LSTM, Prophet, or neural network is used for demand forecasting.

The model learns the relationship between time and historical sales demand.

```text
Time
 ↓
Historical Daily Sales
 ↓
Linear Regression
 ↓
Predicted Daily Demand
 ↓
Future Demand Forecast
```

For example:

```text
Historical demand:

Day 1 → 5 units
Day 2 → 6 units
Day 3 → 6 units
Day 4 → 7 units
Day 5 → 6 units

              ↓

       Linear Regression

              ↓

Predicted Daily Demand
≈ 6 units/day
```

The predicted demand is then used by the inventory intelligence system.

---

# 📊 Actual vs Predicted Sales

The AI dashboard provides an **Actual vs Predicted Sales** graph.

This graph allows users to compare:

```text
Actual Historical Sales
          VS
Predicted Sales
```

This helps visualize how the model is estimating demand based on historical sales behavior.

The graph is displayed using **Chart.js**.

---

# 🏆 Product Performance Analysis

The system analyzes the sales performance of individual products.

Products are categorized based on their historical sales volume.

Possible performance levels include:

```text
Excellent
Good
Average
Low
```

This helps inventory managers identify:

- High-performing products
- Normal-performing products
- Slow-moving products
- Products requiring attention

---

# 📉 Demand Trend Analysis

The Linear Regression model also provides a demand trend.

Products can have trends such as:

```text
Increasing
Stable
Decreasing
```

### Increasing

Demand is showing an upward trend.

### Stable

Demand is relatively consistent.

### Decreasing

Demand is showing a downward trend.

This helps management understand how product demand is changing over time.

---

# 📦 Stock Health Analysis

The system compares:

- Current stock
- Predicted daily demand
- Safety stock
- Lead time
- Reorder point

The result is used to determine the stock health of a product.

Possible stock risk levels include:

```text
Out of Stock
Critical
Low
Medium
Healthy
No Data
```

---

# ⚠️ Intelligent Low-Stock Dashboard

The AI dashboard identifies products that may require inventory attention.

Instead of only checking whether:

```text
quantity < fixed number
```

the system considers predicted demand and inventory coverage.

This makes the low-stock analysis more intelligent and demand-aware.

---

# 📅 Stock Coverage

Stock coverage estimates approximately how many days the current inventory can satisfy the predicted demand.

Conceptually:

```text
Current Stock
     ÷
Predicted Daily Demand
     =
Stock Coverage
```

For example:

```text
Current Stock = 60 units

Predicted Daily Demand = 6 units/day

Stock Coverage ≈ 10 days
```

This allows inventory managers to understand how long the available stock may last.

---

# 📦 Reorder Point

The system calculates a reorder point using predicted demand, lead time, and safety stock.

Conceptually:

```text
Predicted Demand
       +
Lead-Time Demand
       +
Safety Stock
       ↓
Reorder Point
```

The reorder point indicates when inventory should be replenished.

---

# 🔄 Recommended Reorder Quantity

The system also calculates a recommended order quantity.

Conceptually:

```text
Reorder Point
      -
Current Stock
      ↓
Recommended Order Quantity
```

If current stock is already sufficient, the recommended order can be:

```text
0
```

This prevents unnecessary reordering.

---

# 🚦 Inventory Risk Logic

The AI engine evaluates inventory conditions and assigns an appropriate risk level.

```text
                    Inventory
                       |
             +---------+---------+
             |                   |
        No Stock              Stock Available
             |                   |
        Out of Stock       Demand Analysis
                                 |
                    +------------+------------+
                    |            |            |
                 Critical       Low         Healthy
                    |
                 Medium
```

The exact risk depends on the relationship between current stock and predicted demand.

---

# ⚡ Fast-Moving Products

The AI dashboard identifies products with strong sales performance.

Fast-moving products can help management understand:

- Which products sell frequently
- Which products may require higher inventory levels
- Which products contribute significantly to sales

---

# 🐌 Slow-Moving Products

The dashboard also identifies slower-moving products.

This can help management identify:

- Products with lower demand
- Products occupying inventory space
- Products that may require purchasing adjustments

---

# 💡 AI Inventory Recommendations

The dashboard provides inventory recommendations based on the analysis.

Recommendations can help answer questions such as:

- Which products need attention?
- Which products are selling quickly?
- Which products are slow-moving?
- Which products may need replenishment?
- How much stock should be considered for reorder?
- How many days of inventory are currently available?

---

# 🔄 Sales and Inventory Workflow

The system automatically maintains stock when sales are changed.

## Creating a Sale

```text
Create Sale
     ↓
Sales Record Created
     ↓
Product Quantity Decreased
```

Example:

```text
Initial Stock = 50

Sale = 5

New Stock = 45
```

---

## Editing a Sale

When an existing sale is edited, the system calculates the difference between the old and new quantity.

```text
Old Sale Quantity
        ↓
New Sale Quantity
        ↓
Calculate Difference
        ↓
Adjust Product Stock
```

Example:

```text
Old Sale = 5
New Sale = 8

Additional Stock Deduction = 3
```

---

## Deleting a Sale

When a sale is deleted, the sold quantity is returned to inventory.

```text
Delete Sale
     ↓
Recover Sold Quantity
     ↓
Increase Product Stock
```

Example:

```text
Current Stock = 42

Deleted Sale = 5

New Stock = 47
```

---

# 🗄️ Database

The project uses MySQL.

Main database:

```text
inventory_system
```

Main tables include:

```text
categories
media
products
sales
users
user_groups
```

The database SQL file is available inside:

```text
DATABASE FILE/
```

---

# 📁 Project Structure

```text
ai-inventory-management-system/
│
├── admin.php
├── index.php
├── ml_dashboard.php
├── edit_sale.php
├── delete_sale.php
│
├── includes/
│   ├── config.php
│   ├── database.php
│   ├── functions.php
│   └── ...
│
├── layouts/
│   ├── admin_header.php
│   ├── admin_footer.php
│   ├── admin_menu.php
│   └── ...
│
├── ML/
│   ├── ml_engine.py
│   ├── generate_demo_sales.py
│   └── requirements.txt
│
├── DATABASE FILE/
│   └── inventory_system.sql
│
├── uploads/
│
├── .gitignore
├── LICENSE
└── README.md
```

---

# 💻 Installation Guide

This section explains how to install and run the project from the beginning.

Follow the steps in order.

---

## Step 1 — Install XAMPP

Download and install XAMPP.

XAMPP provides:

- Apache
- MySQL
- PHP
- phpMyAdmin

After installing XAMPP, open:

```text
XAMPP Control Panel
```

Start:

```text
Apache
MySQL
```

Both services should be running.

---

# Step 2 — Install Python

Install Python on your computer.

Open PowerShell and check:

```powershell
python --version
```

You should get something similar to:

```text
Python 3.x.x
```

If `python` does not work, try:

```powershell
py --version
```

During Python installation, make sure Python is added to PATH.

---

# Step 3 — Install Git

Install Git.

Check the installation:

```powershell
git --version
```

You should see something similar to:

```text
git version 2.x.x
```

---

# Step 4 — Clone the GitHub Repository

Open PowerShell.

Go to the XAMPP `htdocs` directory:

```powershell
cd C:\xampp\htdocs
```

Clone the project:

```powershell
git clone https://github.com/rohitnandurkar/ai-inventory-management-system.git
```

The project will be downloaded to:

```text
C:\xampp\htdocs\ai-inventory-management-system
```

---

# Step 5 — Verify the Project Folder

Open:

```text
C:\xampp\htdocs
```

You should see:

```text
ai-inventory-management-system
```

Inside the folder, you should see:

```text
admin.php
index.php
ml_dashboard.php
includes
layouts
ML
DATABASE FILE
README.md
```

---

# Step 6 — Start Apache and MySQL

Open XAMPP Control Panel.

Start:

```text
Apache
MySQL
```

Make sure both are running.

---

# Step 7 — Open phpMyAdmin

Open your browser.

Go to:

```text
http://localhost/phpmyadmin
```

---

# Step 8 — Create the Database

In phpMyAdmin:

1. Click **New**
2. Enter the database name:

```text
inventory_system
```

3. Click **Create**

You now have the:

```text
inventory_system
```

database.

---

# Step 9 — Import the Database

Select:

```text
inventory_system
```

in phpMyAdmin.

Click:

```text
Import
```

Click:

```text
Choose File
```

Navigate to:

```text
C:\xampp\htdocs\ai-inventory-management-system\DATABASE FILE\
```

Select:

```text
inventory_system.sql
```

Then click:

```text
Import
```

After successful import, you should see the database tables.

---

# Step 10 — Check Database Configuration

Open:

```text
includes/config.php
```

The default configuration is:

```php
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','inventory_system');
```

If your MySQL username, password, or database configuration is different, update the values accordingly.

---

# Step 11 — Open the Inventory System

Open your browser:

```text
http://localhost/ai-inventory-management-system/
```

The inventory management system should now open.

---

# Step 12 — Login

Use the login account configured for the imported project/database.

Login credentials are intentionally not published in this public README.

For a real deployment, change default/sample credentials and database credentials.

---

# Step 13 — Install Python Dependencies

Open PowerShell.

Go to the project:

```powershell
cd C:\xampp\htdocs\ai-inventory-management-system
```

Go to the ML directory:

```powershell
cd ML
```

Install the Python dependencies:

```powershell
pip install -r requirements.txt
```

The requirements file contains:

```text
mysql-connector-python
pandas
numpy
scikit-learn
```

If `pip` does not work, use:

```powershell
python -m pip install -r requirements.txt
```

---

# Step 14 — Test the ML Engine

From the `ML` folder run:

```powershell
python ml_engine.py 0
```

Here:

```text
0 = Analyze all products
```

The ML engine should return product analytics.

---

# Step 15 — Test a Specific Product

You can test a particular product.

Example:

```powershell
python ml_engine.py 1 7
```

Where:

```text
1 = Product ID
7 = Forecast days
```

The system will analyze the selected product and generate demand predictions.

---

# Step 16 — Open the AI Dashboard

Open:

```text
http://localhost/ai-inventory-management-system/ml_dashboard.php
```

The AI Inventory Analytics dashboard should open.

---

# 📊 AI Dashboard

The AI dashboard provides:

- Products monitored
- Inventory units
- Stock alerts
- Reorder required
- Product selection
- Forecast period
- Current stock
- Predicted daily demand
- Demand trend
- Product performance
- Stock risk
- Reorder point
- Recommended order
- Stock coverage
- Actual vs Predicted Sales
- Product Performance Analysis
- Intelligent Low-Stock Dashboard
- Reorder Center
- AI Inventory Recommendations

---

# 🧪 Optional Demo Sales Data

The project includes:

```text
ML/generate_demo_sales.py
```

This script can be used to generate sample historical sales data for testing the machine learning system.

Run:

```powershell
python generate_demo_sales.py
```

The demo data can be used to test:

- Demand prediction
- Sales trends
- Product performance
- Stock analysis
- Reorder recommendations

> **Important:** Do not repeatedly run the demo-data generator unless you intentionally want to add more demo sales records.

---

# 🔬 Machine Learning Engine

The main ML file is:

```text
ML/ml_engine.py
```

It performs the following tasks:

```text
1. Connect to MySQL
2. Read sales history
3. Aggregate sales by day
4. Prepare historical data
5. Train Linear Regression
6. Predict future demand
7. Analyze demand trend
8. Calculate stock coverage
9. Calculate stock risk
10. Calculate reorder point
11. Calculate recommended order quantity
12. Return product analytics
```

---

# 📅 Forecasting Window

The system analyzes recent sales history to generate demand predictions.

The dashboard allows the user to choose the forecast period.

For example:

```text
7 Days
```

means the system predicts demand for the next seven days.

---

# 🧮 Inventory Intelligence Calculations

The system uses several inventory metrics.

## Predicted Daily Demand

Expected demand for the product per day based on historical sales.

---

## Safety Stock

Additional inventory maintained to reduce the risk of stockouts.

---

## Lead Time

The estimated number of days required for replenishment.

---

## Reorder Point

The inventory level at which replenishment should be considered.

---

## Recommended Order Quantity

The quantity recommended to bring inventory back above the calculated reorder requirement.

---

## Stock Coverage

Estimated number of days that current stock can satisfy predicted demand.

---

# 🖥️ Running the Complete System

The project uses both PHP/MySQL and Python.

### PHP/MySQL

Start:

```text
Apache
MySQL
```

from XAMPP.

### Python

Python is used by the AI engine.

The PHP AI dashboard executes the Python ML engine.

The system therefore requires PHP/Apache to be able to access the Python executable.

---

# 🔧 Troubleshooting

## Problem: Apache does not start

Another application may already be using Apache's port.

Check your XAMPP Apache configuration and make sure the required port is available.

---

## Problem: MySQL does not start

Check whether another MySQL/MariaDB service is already running.

Stop the conflicting service and restart MySQL from XAMPP.

---

## Problem: Database connection error

Check:

```text
includes/config.php
```

Make sure:

```text
DB_HOST = localhost
DB_USER = root
DB_PASS = your MySQL password
DB_NAME = inventory_system
```

---

## Problem: Page not found

Make sure the project is located at:

```text
C:\xampp\htdocs\ai-inventory-management-system
```

Then open:

```text
http://localhost/ai-inventory-management-system/
```

---

## Problem: AI dashboard does not show predictions

First test the Python engine manually:

```powershell
cd C:\xampp\htdocs\ai-inventory-management-system\ML
python ml_engine.py 0
```

If Python works from PowerShell but the dashboard does not show results, Apache/PHP may not be able to find the Python executable.

Check the Python path:

```powershell
where python
```

The PHP configuration may need to use the full path to Python.

---

## Problem: Python package error

Run:

```powershell
python -m pip install -r requirements.txt
```

---

## Problem: Scikit-learn error

Run:

```powershell
python -m pip install scikit-learn
```

---

# 🔐 Security

The repository does not include private login information.

Sensitive files such as:

```text
01 LOGIN DETAILS & PROJECT INFO.txt
```

are excluded from Git using `.gitignore`.

Do not commit:

- Passwords
- API keys
- `.env` files
- Private credentials
- Production database credentials

Before deploying the application publicly, update the database credentials and application login credentials.

---

# 🌐 GitHub Workflow

After making changes to the project:

```powershell
git status
```

Add changes:

```powershell
git add .
```

Commit:

```powershell
git commit -m "Update inventory system"
```

Push to GitHub:

```powershell
git push origin main
```

To get the latest version:

```powershell
git pull origin main
```

---

# 📌 Project URLs

After installation:

### Inventory Application

```text
http://localhost/ai-inventory-management-system/
```

### AI Inventory Dashboard

```text
http://localhost/ai-inventory-management-system/ml_dashboard.php
```

### phpMyAdmin

```text
http://localhost/phpmyadmin
```

---

# 🎯 Project Objective

The objective of this project is to extend a traditional inventory management system with machine learning capabilities.

The system uses historical sales data and Linear Regression to provide:

- Demand forecasting
- Inventory health analysis
- Stock risk detection
- Product performance analysis
- Stock coverage estimation
- Reorder point calculation
- Intelligent reorder recommendations

This allows inventory decisions to be based not only on current stock levels but also on historical sales behavior and predicted demand.

---

# 🎓 Academic Project

This project demonstrates the integration of:

```text
Web Development
       +
Database Management
       +
Python
       +
Machine Learning
       +
Data Visualization
       +
Inventory Management
```

It can be used as a college/final-year project to demonstrate the practical application of machine learning in inventory management.

---

# 🚀 Future Enhancements

Possible future improvements include:

- Supplier management
- Purchase order management
- Automated purchase orders
- Email stock alerts
- WhatsApp notifications
- Advanced inventory reports
- More detailed analytics
- Role-based access improvements
- AI inventory assistant
- Automated daily analytics
- Cloud deployment
- REST API
- Mobile application
- Multi-database support

---

# 👨‍💻 Author

**Rohit Nandurkar**

AI & ML Engineering

GitHub:

https://github.com/rohitnandurkar/ai-inventory-management-system

---

# ⭐ Project Summary

```text
AI INVENTORY MANAGEMENT SYSTEM

        Inventory Management
                 +
             Sales
                 +
           MySQL Database
                 +
              Python
                 +
        Linear Regression
                 +
        Demand Prediction
                 +
         Stock Analysis
                 +
       Reorder Intelligence
                 ↓
       AI Inventory Dashboard
```

Built with:

**PHP + MySQL + Python + Linear Regression + Bootstrap + Chart.js**
