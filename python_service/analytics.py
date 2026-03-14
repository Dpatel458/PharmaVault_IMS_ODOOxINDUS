import os
import sys
import mysql.connector
import pandas as pd
from datetime import datetime

def get_db_connection():
    try:
        conn = mysql.connector.connect(
            host="127.0.0.1",
            user="root",
            password="",
            database="ims"
        )
        return conn
    except mysql.connector.Error as e:
        print(f"Error connecting to MySQL: {e}")
        return None

def generate_weekly_report():
    print(f"[{datetime.now()}] Generating weekly inventory report...")
    conn = get_db_connection()
    if not conn:
        print("Could not connect to database. Exiting.")
        sys.exit(1)

    # 1. Fetch current stock levels
    stock_query = """
        SELECT code, p.name as product_name, c.name as category, s.quantity, s.warehouse_id
        FROM stock s
        JOIN product p ON s.product_id = p.id
        LEFT JOIN category c ON p.category_id = c.id
    """
    try:
        df_stock = pd.read_sql(stock_query, conn)
        
        # 2. Basic Demand Prediction Strategy:
        # Check stock ledgers for the last 30 days to see average daily consumption (Deliveries)
        ledger_query = """
            SELECT product_id, SUM(quantity_change) as total_consumption
            FROM stock_ledger
            WHERE movement_type = 'delivery' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY product_id
        """
        df_consumption = pd.read_sql(ledger_query, conn)
        df_consumption['total_consumption'] = df_consumption['total_consumption'].abs() # Deliveries are negative

        # Mock generating a CSV
        report_filename = f"weekly_report_{datetime.now().strftime('%Y%m%d')}.csv"
        df_stock.to_csv(report_filename, index=False)
        print(f"[{datetime.now()}] Report saved as {report_filename} in the current directory.")
        
    except Exception as e:
        print(f"Error processing data: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    generate_weekly_report()
