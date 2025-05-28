import pandas as pd
from sqlalchemy import create_engine
from prophet import Prophet

# ── CONFIGURE YOUR DB HERE ─────────────────────────────────────────────────
DB_USER     = 'root'
DB_PASSWORD = ''
DB_HOST     = 'localhost'
DB_NAME     = 'scms'
# ─────────────────────────────────────────────────────────────────────────────

def get_engine():
    url = f"mysql+pymysql://{DB_USER}:{DB_PASSWORD}@{DB_HOST}/{DB_NAME}"
    return create_engine(url)

def load_daily_sales():
    """
    Loads daily product sales (quantities and price) from your DB.
    """
    engine = get_engine()
    sql = """
        SELECT
          p.NAME           AS product_name,
          DATE(t.`DATE`)   AS ds,
          SUM(td.QTY)      AS y,
          -- we'll grab the medium price for revenue calc later
          MAX(p.price_medium) AS price_medium
        FROM `transaction` t
        JOIN transaction_details td ON td.TRANS_ID = t.TRANS_ID
        JOIN product p             ON p.NAME     = td.PRODUCT
        GROUP BY product_name, ds
        ORDER BY product_name, ds;
    """
    return pd.read_sql(sql, engine, parse_dates=['ds'])

def forecast_events(df, events, forecast_days=365):
    """
    For each product, train a daily Prophet model, forecast next year,
    aggregate predicted qty/revenue per event.
    Returns a DataFrame event_name × product_name × forecast_qty × forecast_revenue.
    """
    engine = get_engine()
    results = []

    # train/forecast per product:
    for product, grp in df.groupby('product_name'):
        if grp.shape[0] < 2:
            print(f"Skipping '{product}' (only {grp.shape[0]} days of history)")
            continue

        model = Prophet(yearly_seasonality=True, weekly_seasonality=True, daily_seasonality=False)
        model.fit(grp[['ds','y']].rename(columns={'y':'y'}))
        future = model.make_future_dataframe(periods=forecast_days)
        fc = model.predict(future)[['ds','yhat']]

        # attach price to forecast rows (same for all days)
        price = grp['price_medium'].iloc[0]
        fc['forecast_revenue'] = fc['yhat'] * price
        fc['product_name'] = product

        # now aggregate per event:
        for name, start, end in events:
            mask = (fc['ds'] >= pd.to_datetime(start)) & (fc['ds'] <= pd.to_datetime(end))
            sub = fc.loc[mask]
            if sub.empty:
                qty = 0
                rev = 0
            else:
                qty = sub['yhat'].sum()
                rev = sub['forecast_revenue'].sum()
            results.append({
                'event_name': name,
                'product_name': product,
                'forecast_qty': qty,
                'forecast_revenue': rev
            })

    return pd.DataFrame(results)

def save_event_forecast(df):
    engine = get_engine()
    df.to_sql('event_product_forecast', engine, if_exists='replace', index=False)
    print("→ event_product_forecast table updated.")

def main():
    # Define your events (matching your mockups)
    year = pd.Timestamp.now().year + 1  # forecast next year
    events = [
        ('Summer break',        f'{year}-03-01', f'{year}-05-31'),
        ('Rainy season',        f'{year}-06-01', f'{year}-11-30'),
        ('Valentine’s Day',     f'{year}-02-14', f'{year}-02-14'),
        ('Christmas Day',       f'{year}-12-25', f'{year}-12-25'),
        ('New Year’s Day',      f'{year}-01-01', f'{year}-01-01'),
        ('First day of classes',f'{year}-06-01', f'{year}-06-07'),
        ('Graduation Ceremonies',f'{year}-03-01',f'{year}-04-30'),
    ]

    print("Loading daily sales…")
    df_daily = load_daily_sales()

    print("Forecasting events… this may take a few minutes.")
    df_evt = forecast_events(df_daily, events)

    print("Saving to DB…")
    save_event_forecast(df_evt)
    print("Done.")
    
if __name__ == "__main__":
    main()
