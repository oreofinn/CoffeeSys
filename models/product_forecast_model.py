import pandas as pd
from sqlalchemy import create_engine
from prophet import Prophet

# ─── UPDATE YOUR DATABASE CREDENTIALS HERE ───────────────────────────────────
DB_USER     = 'root'
DB_PASSWORD = ''
DB_HOST     = 'localhost'
DB_NAME     = 'scms'
# ─────────────────────────────────────────────────────────────────────────────

def get_engine():
    url = f'mysql+pymysql://{DB_USER}:{DB_PASSWORD}@{DB_HOST}/{DB_NAME}'
    return create_engine(url)

def load_product_yearly():
    engine = get_engine()
    query = """
      SELECT
        p.NAME AS product_name,
        -- escaped %%Y so Python won’t try to format it
        DATE_FORMAT(t.`DATE`, '%%Y-01-01') AS ds,
        SUM(td.QTY) AS y
      FROM `transaction` t
      JOIN transaction_details td ON td.TRANS_ID = t.TRANS_ID
      JOIN product p             ON p.NAME     = td.PRODUCT
      GROUP BY product_name, ds
      ORDER BY product_name, ds
    """
    df = pd.read_sql(query, engine, parse_dates=['ds'])
    return df

def forecast_per_product(df, forecast_years=3):
    """
    For each product in df, train Prophet on its yearly data and forecast forward.
    Returns a DataFrame with columns [product_name, ds, yhat].
    """
    results = []
    for product, group in df.groupby('product_name'):
        # --- Skip if fewer than 2 data points ---
        if group.shape[0] < 2:
            print(f"Skipping '{product}': only {group.shape[0]} year(s) of data.")
            continue

        # Prophet expects columns ds (datetime) and y (float)
        m = Prophet(
            yearly_seasonality=True,
            weekly_seasonality=False,
            daily_seasonality=False
        )
        m.fit(group.rename(columns={'ds': 'ds', 'y': 'y'}))

        # --- Use 'YE' instead of deprecated 'Y' for annual freq ---
        future = m.make_future_dataframe(periods=forecast_years, freq='YE')
        forecast = m.predict(future)

        # Keep only the forecast rows (where ds > last historical date)
        last_hist = group['ds'].max()
        future_forecast = forecast[forecast['ds'] > last_hist][['ds', 'yhat']]
        future_forecast['product_name'] = product
        results.append(future_forecast[['product_name', 'ds', 'yhat']])

    if not results:
        raise ValueError("No product had enough history to forecast.")
    return pd.concat(results, ignore_index=True)

def save_product_forecast(df):
    """
    Save to MySQL table product_yearly_forecast (product_name, forecast_year, yhat).
    """
    engine = get_engine()
    to_save = df.copy()
    # Rename ds -> forecast_year date
    to_save = to_save.rename(columns={'ds':'forecast_year'})
    to_save.to_sql('product_yearly_forecast', engine, if_exists='replace', index=False)
    print("→ product_yearly_forecast table updated.")

def main():
    print("Loading historical product-year data…")
    df_hist = load_product_yearly()
    print(f"  {df_hist['product_name'].nunique()} products × {df_hist['ds'].nunique()} years of history")

    print("Forecasting each product…")
    df_fore = forecast_per_product(df_hist, forecast_years=3)

    print("Saving forecasts to DB…")
    save_product_forecast(df_fore)
    print("Done. You can now query product_yearly_forecast for your best-sellers per year.")

if __name__ == "__main__":
    main()
